require('./bootstrap');
require('./card-canvas');

window.Vue = require('vue');

document.addEventListener('DOMContentLoaded', function () {
    configurePagination();
    configureChangeFileEvent();
});

/**
 * Agregar clases css a elementos de paginación.
 */
function configurePagination() {
    const paginations = document.querySelectorAll('.pagination');
    paginations.forEach(x => x.classList.add('pagination-sm', 'm-0'));
}

/**
 * Detectar evento de selección de imagen en tarjeta.
 */
function configureChangeFileEvent() {
    const fileFields = document.querySelectorAll('.file-field');
    fileFields.forEach(el => {
        el.addEventListener('change', function (e) {
            let fileName = e.target.files[0].name;
            let nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    });
}
