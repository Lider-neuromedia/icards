document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('#canvas-card')) {
        initCardCanvas(window.card);
    }
});

async function initCardCanvas(card) {
    const imageQR = await loadImage(card.imageQR);

    const canDrawLogo = card.canDrawLogo == undefined || card.canDrawLogo == true;
    const canDrawCompany = card.canDrawCompany == undefined || card.canDrawCompany == true;

    const imageLogo = await loadImage(card.imageLogo);
    const imageWidth = imageLogo.naturalWidth;
    const imageHeight = imageLogo.naturalHeight;
    const imageWidthResize = 250;
    const imageHeightResize = Math.round((imageWidthResize * imageHeight) / imageWidth);

    const canvas = document.getElementById('canvas-card');
    canvas.setAttribute('height', canvas.clientHeight + imageHeightResize);

    const ctx = canvas.getContext('2d');
    const canvasWidth = ctx.canvas.clientWidth;
    const canvasHeight = ctx.canvas.clientHeight;

    // Base
    ctx.shadowBlur = 10;
    ctx.shadowColor = "black";
    ctx.fillStyle = '#ffffff';
    ctx.semiRoundRect(10, 10, 300, canvasHeight - 20, 30).fill();

    // Borde
    ctx.shadowBlur = 0;
    ctx.strokeStyle = card.mainColor;
    ctx.lineWidth = 4;
    ctx.semiRoundRect(20, 20, 280, canvasHeight - 40, 25).stroke();

    // Logo
    const left = (canvasWidth - imageWidthResize) / 2;
    if (canDrawLogo) {
        ctx.drawImage(imageLogo, left, canDrawCompany ? 20 : 40, imageWidthResize, imageHeightResize);
    }

    // Título
    const textColor = getComputedStyle(document.querySelector('body')).getPropertyValue('--text-color');
    ctx.lineWidth = 1.0;
    ctx.font = "bolder 19px 'Exo', sans-serif";
    ctx.fillStyle = textColor;
    ctx.textAlign = 'center';

    let top = canDrawLogo ? 65 + imageHeightResize : 65 + imageHeightResize * 0.3;
    top = canDrawLogo && canDrawCompany ? top - 40 : top;
    textLines(decodeHTMLEntities(card.name)).forEach(text => {
        ctx.fillText(text, canvasWidth / 2, top += 20);
    });

    ctx.font = "normal 16px 'Exo', sans-serif";
    textLines(decodeHTMLEntities(card.cargo)).forEach(text => {
        ctx.fillText(text, canvasWidth / 2, top += 20);
    });

    if (canDrawCompany) {
        ctx.font = "bolder 19px 'Exo', sans-serif";
        textLines(decodeHTMLEntities(card.company)).forEach(text => {
            ctx.fillText(text, canvasWidth / 2, top += canDrawLogo ? 35 : 50);
        });
    }

    // QR
    let w = (canvasWidth - 230) / 2;
    let h = canDrawLogo ? top + 28 : top + 40;
    ctx.lineWidth = 4;
    ctx.drawImage(imageQR, w, h, 230, 230);
    ctx.roundRect(w, h, 230, 230, 25).stroke();

    // Descargar Canvas
    const downloadButton = document.querySelector('#donwload-canvas-button');
    downloadButton.addEventListener('click', () => makeImage(canvas, ctx));
}

function loadImage(url) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.src = url;
        image.onload = () => resolve(image);
        image.onerror = reject
    });
}

function decodeHTMLEntities(text) {
    const el = document.createElement('textarea');
    el.innerHTML = text;
    const value = el.value;
    return value;
}

function textLines(text) {
    if (text.length > 25) {
        const words = text.split(" ");
        const lines = [];
        let temporal = "";

        for (let i = 0; i < words.length; i++) {
            const word = words[i];

            if ((temporal.length + word.length) >= 25) {
                lines.push(temporal);
                temporal = "";
            }

            temporal += temporal.length == 0 ? word : ` ${word}`;

            if (i == (words.length - 1)) {
                lines.push(temporal);
                temporal = "";
            }
        }

        return lines;
    }

    return [text];
}

function makeImage(canvas, ctx) {
    const img1 = new Image();

    img1.onload = function () {
        canvas.width = img1.width;
        canvas.height = img1.height;
        ctx.drawImage(img1, 0, 0);
    };
    img1.src = document.getElementById("image1").src;

    const img2 = new Image();
    img2.src = canvas.toDataURL("image/jpeg");
    document.body.appendChild(img2);

    const link = document.createElement("a");
    link.download = "image.png";

    canvas.toBlob(function (blob) {
        link.href = URL.createObjectURL(blob);
        link.click();
        setTimeout(() => img2.remove(), 100);
    }, "image/png");

}

CanvasRenderingContext2D.prototype.roundRect = function (x, y, w, h, r) {
    if (w < 2 * r) r = w / 2;
    if (h < 2 * r) r = h / 2;
    this.beginPath();
    this.moveTo(x + r, y);
    this.arcTo(x + w, y, x + w, y + h, r);
    this.arcTo(x + w, y + h, x, y + h, r);
    this.arcTo(x, y + h, x, y, r);
    this.arcTo(x, y, x + w, y, r);
    this.closePath();
    return this;
}

CanvasRenderingContext2D.prototype.semiRoundRect = function (x, y, w, h, r) {
    if (w < 2 * r) r = w / 2;
    if (h < 2 * r) r = h / 2;
    this.beginPath();
    this.moveTo(x + r, y);
    this.arcTo(x + w, y, x + w, y + h, r);
    this.arcTo(x + w, y + h, x, y + h, 0);
    this.arcTo(x, y + h, x, y, r);
    this.arcTo(x, y, x + w, y, 0);
    this.closePath();
    return this;
}
