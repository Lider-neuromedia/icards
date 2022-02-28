require('./bootstrap');

document.addEventListener('DOMContentLoaded', function () {
    trackVisit();
    trackScannig();
    trackCustomEvents();
});

function trackCustomEvents() {
    const elements = document.querySelectorAll('.track-event');
    const cardId = document.querySelector('meta[name="analytics-card-id"]').content;

    elements.forEach(el => {
        el.addEventListener('click', async function (e) {
            const event = this.dataset.event;

            if (isCorrectValue(event) && isCorrectValue(cardId)) {
                console.log(event);
                await trackAction(event, {
                    cardId
                });
            }
        });
    });
}

function isCorrectValue(value) {
    return value != undefined && value != null && value.trim() != '';
}

async function trackVisit() {
    const cardId = document.querySelector('meta[name="analytics-card-id"]').content;

    await trackAction('visit-card', {
        cardId
    });
}

async function trackScannig() {
    const cardId = document.querySelector('meta[name="analytics-card-id"]').content;
    const urlParams = new URLSearchParams(window.location.search);

    if (!urlParams.has('action')) return false;
    if (urlParams.get('action') != 'scan') return false;

    await trackAction('scan-card', {
        cardId
    });

    setTimeout(() => {
        let newUrl = window.location.href;
        newUrl = newUrl.substring(0, newUrl.indexOf(window.location.search));
        window.history.pushState('', document.title, newUrl);
    }, 300);
}

async function trackAction(action, data) {
    const url = document.querySelector('meta[name="url-root"]').content;

    try {

        await axios.post(`${url}/analytics/track`, {
            action: action,
            data: data,
        });

    } catch (error) {
        console.log(error.response);
    }
}
