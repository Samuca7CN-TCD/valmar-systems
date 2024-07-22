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
    } else if(format === 'new_date') {
        return date.toISOString().substring(0, 10);
    }else{
        const currentDate = date ? new Date(date + ' 00:00:00') : new Date();
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const day = currentDate.getDate().toString().padStart(2, '0');
        
        return `${day}/${month}/${year}`;
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
