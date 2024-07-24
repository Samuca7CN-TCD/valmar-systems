export const toMoney = (value) => {
    return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }).toString()
}

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

export const formatDate = (date = null, format = null) => {
    if (format === null) {
        const currentDate = date ? new Date(date + ' 00:00:00') : new Date();
        return currentDate.toISOString().substring(0, 10);
    } else if (format === 'new_date') {
        return date.toISOString().substring(0, 10);
    } else {
        const currentDate = date ? new Date(date + ' 00:00:00') : new Date();
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const day = currentDate.getDate().toString().padStart(2, '0');

        return `${day}/${month}/${year}`;
    }
};

export const formatPhoneNumber = (phone_number, format = null) => {
    if (!format) {
        // Remove todos os caracteres que não são números
        const cleaned = ('' + phone_number).replace(/\D/g, '');

        // Verifica se o número tem o código de país (+55)
        if (cleaned.length === 13 && cleaned.startsWith('55')) {
            const country = cleaned.slice(0, 2); // 55
            const area = cleaned.slice(2, 4); // 73
            const firstPart = cleaned.slice(4, 5); // 9
            const secondPart = cleaned.slice(5, 9); // 8812
            const thirdPart = cleaned.slice(9); // 1518

            return `(${area}) ${firstPart} ${secondPart}-${thirdPart}`;
        } else {
            // Caso não tenha o formato esperado, retorna o número original
            return phone_number;
        }
    } else {
        // Implementação para outros formatos, se necessário
    }
};


export const calcDeadlineDays = (deadline) => {
    const date1 = Date.parse(deadline + ' 17:30:00')
    const date2 = Date.now()
    const diff = date1 - date2
    const div = 1000 * 60 * 60 * 24
    const dias = Math.round(diff / div)

    return dias < 0 ? 0 : dias
}
