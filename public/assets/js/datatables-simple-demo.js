window.addEventListener('DOMContentLoaded', event => {

    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    const datatablesSimple = document.getElementById('datatablesSimple');

    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            labels: {
                placeholder: "Buscar...",
                perPage: "registros por página",
                noRows: "No se encontraron registros",
                info: "Mostrando {start} a {end} de {rows} registros"
            },
            firstLast: {
                first: "Primero",
                last: "Último"
            },
            prevNext: {
                previous: "Anterior",
                next: "Siguiente"
            }
        });
    }

});