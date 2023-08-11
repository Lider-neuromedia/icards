require('./bootstrap');
require('./card-canvas');
require('./allowed-accounts');

window.Vue = require('vue');

document.addEventListener('DOMContentLoaded', function () {
    configurePagination();
    configureChangeFileEvent();

    // Fields
    configureFieldsEvents();
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

function configureFieldsEvents() {
    const groups = window.groups;

    if (!groups) return false;

    const fields = [];

    for (const groupKey in groups) {
        if (Object.hasOwnProperty.call(groups, groupKey)) {
            const group = groups[groupKey];

            group.values.forEach(field => {
                field.field_key = `${groupKey}_${field.key}`;
                field.field_key_wp = `${groupKey}_${field.key}_wp`;
                fields.push(field);
            });
        }
    }

    const watchFields = fields.filter(x => x.watch == true);
    const conditionallyVisibleFields = fields.filter(x => {
        return x.visible_when != undefined &&
            x.visible_when != null &&
            x.visible_when.trim() != "";
    });

    watchFields.forEach(field => {
        const fieldInput = document.getElementById(field.field_key);

        if (fieldInput) {
            // Validar estados iniciales.
            if (field.type == 'boolean') {
                toggleFieldVisibility(conditionallyVisibleFields, field.key, fieldInput.checked ? "1" : "0");
            } else if (field.type == 'select') {
                toggleFieldVisibility(conditionallyVisibleFields, field.key, fieldInput.value);
            }

            // Validar valores al ejecutar evento de cambio de valor.
            fieldInput.addEventListener('change', function (event) {
                if (field.type == 'boolean') {
                    toggleFieldVisibility(conditionallyVisibleFields, field.key, fieldInput.checked ? "1" : "0");
                } else if (field.type == 'select') {
                    toggleFieldVisibility(conditionallyVisibleFields, field.key, fieldInput.value);
                }
            });
        }
    });
}

function toggleFieldVisibility(fields, fieldKey, value) {
    fields.forEach(x => {
        const temp = x.visible_when.split(":");
        const targetKeyField = temp[0];
        const targetValueField = temp[1];
        const fieldTarget = document.getElementById(x.field_key_wp);

        if (targetKeyField == fieldKey) {
            if (targetValueField == value) {
                fieldTarget.style.display = "block";
            } else {
                fieldTarget.style.display = "none";
            }
        }
    });
}
