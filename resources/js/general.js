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
