export const toMoney = (value, options = {}) => {
    // 1. Garante que o valor seja um número.
    // Se não for um número válido (incluindo null, undefined, strings não numéricas),
    // o padrão será 0.
    let numericValue = Number(value);
    if (isNaN(numericValue)) {
        numericValue = 0;
    }

    // 2. Opções de Formatação Padrão
    const defaultOptions = {
        style: 'currency',
        currency: 'BRL',
        // O toLocaleString já lida com o sinal negativo.
        // Por exemplo: -100.50 -> -R$ 100,50
        // Para controlar como o sinal é exibido (ex: sempre, exceto zero, etc.),
        // a propriedade signDisplay pode ser útil, mas o padrão 'auto' geralmente funciona.
        // signDisplay: 'auto', // Padrão
    };

    // Mescla as opções padrão com as opções passadas (permitindo customização)
    const formatOptions = { ...defaultOptions, ...options };

    // 3. Usa o toLocaleString nativo para lidar com positivos, negativos e zero.
    return numericValue.toLocaleString('pt-BR', formatOptions).toString();
};

export const measurementUnitResolver = (measurement_units_list, measurement_unit_id, quantity, type = 'geral') => {
    const mu = measurement_units_list.find(mu => mu.id === measurement_unit_id)
    if (mu) {
        if (type == 'geral') {
            if (quantity == 1) return mu.name
            return mu.name_plural
        }
        return mu.name
    }
    return "unidade de medida não identificada"
}

const months = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

export const formatDate = (date = null, format = null) => {
    if (!date) date = new Date();

    const currentDate = new Date(date);

    if (format === null || format === 'new_date') {
        return currentDate.toISOString().substring(0, 10); //YYYY-MM-DD
    } else if (format === 'reading') {
        const months = [
            'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
            'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'
        ];
        const day = currentDate.getDate();
        const month = months[currentDate.getMonth()];
        const year = currentDate.getFullYear();

        return `${day} de ${month} de ${year}`;
    } else if (format === 'reading_date_time') {
        const day = String(currentDate.getDate()).padStart(2, '0');
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const year = currentDate.getFullYear();
        const hours = String(currentDate.getHours()).padStart(2, '0');
        const minutes = String(currentDate.getMinutes()).padStart(2, '0');
        const seconds = String(currentDate.getSeconds()).padStart(2, '0');

        return `${day}/${month}/${year} às ${hours}:${minutes}:${seconds}`;
    } else {
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const day = currentDate.getDate().toString().padStart(2, '0');

        return `${day}/${month}/${year}`;
    }
};




export const formatPhoneNumber = (phone_number) => {
    // Remove todos os caracteres que não são números
    let cleaned = clearFormat(phone_number);
    // Verifica se o número tem o código de país (+55)
    if (cleaned.length < 11) {
        return cleaned;
    } else {
        if (cleaned.startsWith('55')) {
            cleaned = cleaned.substring(2);
        }
        const area = cleaned.substring(0, 2);
        const nine = cleaned.substring(2, 3);
        const firstPart = cleaned.substring(3, 7);
        const secondPart = cleaned.substring(7);

        return `(${area}) ${nine} ${firstPart}-${secondPart}`;
    }
};

export const validateCPF = (cpf) => {
    // Remove todos os caracteres que não são números
    let cleaned = clearFormat(cpf);

    // Verifica se o CPF tem 11 dígitos
    if (cleaned.length !== 11) {
        return false;
    }

    // Verifica se todos os dígitos são iguais (casos inválidos como 111.111.111-11)
    const invalidCPFs = [
        '00000000000', '11111111111', '22222222222', '33333333333',
        '44444444444', '55555555555', '66666666666', '77777777777',
        '88888888888', '99999999999'
    ];
    if (invalidCPFs.includes(cleaned)) {
        return false;
    }

    // Função para calcular o dígito verificador
    const calculateCheckDigit = (cpf, length) => {
        let sum = 0;
        let multiplier = length + 1;
        for (let i = 0; i < length; i++) {
            sum += parseInt(cpf.charAt(i)) * multiplier--;
        }
        const remainder = (sum * 10) % 11;
        return (remainder === 10 || remainder === 11) ? 0 : remainder;
    };

    // Calcula os dois dígitos verificadores
    const firstCheckDigit = calculateCheckDigit(cleaned, 9);
    const secondCheckDigit = calculateCheckDigit(cleaned, 10);

    // Verifica se os dígitos verificadores são válidos
    return firstCheckDigit === parseInt(cleaned.charAt(9)) &&
        secondCheckDigit === parseInt(cleaned.charAt(10));
};

export const formatCPF = (cpf) => {
    // Remove todos os caracteres que não são números
    let cleaned = clearFormat(cpf);

    // Verifica se o CPF tem 11 dígitos
    if (cleaned.length !== 11) {
        return cleaned;
    }

    const part1 = cleaned.substring(0, 3);
    const part2 = cleaned.substring(3, 6);
    const part3 = cleaned.substring(6, 9);
    const part4 = cleaned.substring(9, 11);

    return `${part1}.${part2}.${part3}-${part4}`;
};

// =============================================
// FUNÇÕES PARA CNPJ


export const validateCNPJ = (cnpj) => {
    let cleaned = clearFormat(cnpj);

    if (cleaned.length !== 14) {
        return false;
    }

    // Elimina CNPJs inválidos conhecidos
    const invalidCNPJs = [
        '00000000000000', '11111111111111', '22222222222222', '33333333333333',
        '44444444444444', '55555555555555', '66666666666666', '77777777777777',
        '88888888888888', '99999999999999'
    ];
    if (invalidCNPJs.includes(cleaned)) {
        return false;
    }

    // Valida DVs
    let length = cleaned.length - 2;
    let numbers = cleaned.substring(0, length);
    let digits = cleaned.substring(length);
    let sum = 0;
    let pos = length - 7;
    let multiplier;

    for (let i = length; i >= 1; i--) {
        sum += parseInt(numbers.charAt(length - i)) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }
    let result = sum % 11 < 2 ? 0 : 11 - (sum % 11);
    if (result !== parseInt(digits.charAt(0))) {
        return false;
    }

    length = length + 1;
    numbers = cleaned.substring(0, length);
    sum = 0;
    pos = length - 7;

    for (let i = length; i >= 1; i--) {
        sum += parseInt(numbers.charAt(length - i)) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }
    result = sum % 11 < 2 ? 0 : 11 - (sum % 11);
    if (result !== parseInt(digits.charAt(1))) {
        return false;
    }
    return true;
};

export const formatCNPJ = (cnpj) => {
    let cleaned = clearFormat(cnpj);

    if (cleaned.length !== 14) {
        return cleaned; // Retorna sem formatar se o tamanho for incorreto
    }

    // Formata o CNPJ: XX.XXX.XXX/XXXX-XX
    const part1 = cleaned.substring(0, 2);
    const part2 = cleaned.substring(2, 5);
    const part3 = cleaned.substring(5, 8);
    const part4 = cleaned.substring(8, 12);
    const part5 = cleaned.substring(12, 14);

    return `${part1}.${part2}.${part3}/${part4}-${part5}`;
};


// =============================================
// FUNÇÕES PARA CEP

export const formatarCEP = (cep) => {
    // Remove tudo que não for número
    let cleaned = String(cep).replace(/\D/g, ''); // Garante que é string e remove não-dígitos

    // Verifica se tem 8 dígitos
    if (cleaned.length !== 8) {
        return cleaned; // Retorna o CEP limpo se não tiver 8 dígitos para permitir o usuário continuar digitando
    }

    // Formata o CEP: XXXXX-XXX
    return cleaned.substring(0, 5) + '-' + cleaned.substring(5, 8);
};


// =============================================
// RESTANTE DO GENERAL.JS

export const formatAgency = (agency) => {
    // Remove todos os caracteres que não são números
    let cleaned = clearFormat(agency);
    return cleaned;
};

export const formatBankAccount = (account) => {
    // Remove todos os caracteres que não são números
    let cleaned = clearFormat(account);

    // Verifica se a conta tem pelo menos 2 dígitos (um número de conta e um dígito verificador)
    if (cleaned.length < 2 || cleaned.length > 13) {
        return cleaned;
    }

    const mainPart = cleaned.substring(0, cleaned.length - 1);
    const digit = cleaned.substring(cleaned.length - 1);

    return `${mainPart}-${digit}`;
};

export const validatePixToken = (key) => {
    const re = /^[a-zA-Z0-9]{32,36}$/;
    return re.test(String(key));
};

export const clearFormat = (text) => {
    // Remove todos os caracteres que não são números
    return ('' + text).replace(/\D/g, '');
};

export const calcDeadlineDays = (deadline) => {
    const date1 = Date.parse(deadline + ' 17:30:00')
    const date2 = Date.now()
    const diff = date1 - date2
    const div = 1000 * 60 * 60 * 24
    const dias = Math.round(diff / div)

    return dias < 0 ? 0 : dias
}

export const absolute = (value) => {
    return value < 0 ? -value : value
}

export const printList = () => {
    window.print()
}

export const getPaymentMethodLabel = (method) => {
    const map = {
        pix: 'Pix',
        dinheiro: 'Dinheiro',
        cartao_credito: 'Cartão de Crédito',
        cartao_debito: 'Cartão de Débito',
        ted: 'TED',
        cheque: 'Cheque',
    };
    return map[method] || method;
}