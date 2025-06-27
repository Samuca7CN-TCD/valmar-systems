<?php

namespace App\Traits;

use App\Models\Action;
use App\Models\Procedure;
use App\Models\Record;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Registra os observadores de eventos do modelo.
     */
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->recordActivity('create');
        });

        static::updated(function (Model $model) {
            $model->recordActivity('update');
        });

        static::deleted(function (Model $model) {
            $model->recordActivity('delete');
        });
    }

    /**
     * Método público para registrar a atividade.
     * Determina o tipo de ação e delega para o método de gravação.
     *
     * @param string $actionType 'create', 'update', 'delete', ou um nome de ação personalizado
     */
    public function recordActivity(string $actionType): void
    {
        if ($actionType === 'update' &&!$this->wasChanged()) {
            return;
        }

        $before = in_array($actionType, ['update', 'delete'])? $this->getOriginal() : null;
        $after = in_array($actionType, ['create', 'update'])? $this->getAttributes() : null;

        // Para ações personalizadas, o estado "antes" é nulo e o "depois" é o estado atual.
        if (!in_array($actionType, ['create', 'update', 'delete'])) {
            $before = null;
            $after = $this->getAttributes();
        }

        $this->saveAuditTrail($actionType, $before, $after);
    }
    
    /**
     * Lógica centralizada para salvar a Procedure e o Record.
     *
     * @param string $actionName O nome da ação (pode ser 'create', 'update', 'delete', ou um nome personalizado).
     * @param array|null $before O estado do modelo antes da mudança.
     * @param array|null $after O estado do modelo após a mudança.
     */
    private function saveAuditTrail(string $actionName,?array $before,?array $after): void
    {
        try {
            if (!Auth::check()) return;

            // Filtra campos que não devem ser logados
            if (property_exists($this, 'doNotLogFields') && is_array($this->doNotLogFields)) {
                foreach ($this->doNotLogFields as $field) {
                    unset($before[$field], $after[$field]);
                }
            }

            $action = Action::where('type', 'like', $actionName)->first();
            if (!$action) {
                Log::warning("Ação de auditoria '{$actionName}' não encontrada na tabela 'actions'.");
                return;
            }

            // **MUDANÇA CRÍTICA:** A responsabilidade de determinar o departamento
            // é totalmente delegada ao método getDepartmentIdForAudit() do modelo.
            $departmentId = $this->getDepartmentIdForAudit();

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => $action->id,
                'department_id' => $departmentId,
            ]);

            Record::create([
                'procedure_id' => $procedure->id,
                'before_state' => $before,
                'after_state' => $after,
                'auditable_id' => $this->getKey(),
                'auditable_type' => get_class($this),
            ]);

        } catch (\Exception $e) {
            Log::error('Falha ao gravar o rastro de auditoria: '. $e->getMessage());
        }
    }
}