document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('#canvas-card')) {
        initCardCanvas(window.card);
    }
});

async function initCardCanvas(card) {
    const imageQR = await loadImage(card.imageQR);

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
    ctx.drawImage(imageLogo, left, 40, imageWidthResize, imageHeightResize);

    // Título
    const textColor = getComputedStyle(document.querySelector('body')).getPropertyValue('--text-color');
    ctx.lineWidth = 1.0;
    ctx.font = "19px 'Exo', sans-serif";
    ctx.fillStyle = textColor;
    ctx.textAlign = 'center';

    let top = 65 + imageHeightResize;
    textLines(card.name).forEach(text => {
        ctx.fillText(text, canvasWidth / 2, top += 20);
    });
    textLines(card.cargo).forEach(text => {
        ctx.fillText(text, canvasWidth / 2, top += 20);
    });

    // QR
    let w = (canvasWidth - 230) / 2;
    let h = top + 35;
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

function textLines(text) {
    if (text.length > 25) {
        let first = text.substring(0, text.length / 2);
        let second = text.substring(text.length / 2, text.length);

        if (first.substring(first.length - 1) != ' ' && second.substring(0, 1) != ' ') {
            first += '-';
        }
        first = first.trim();
        second = second.trim();

        return [
            first,
            second,
        ];
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
