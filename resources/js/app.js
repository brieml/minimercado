const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('sidebar-backdrop');
const toggles = document.querySelectorAll('[data-sidebar-toggle]');

const setSidebarOpen = (open) => {
    sidebar?.classList.toggle('-translate-x-full', !open);
    backdrop?.classList.toggle('hidden', !open);
    toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(open)));
};

toggles.forEach((toggle) => {
    toggle.addEventListener('click', () => {
        setSidebarOpen(sidebar.classList.contains('-translate-x-full'));
    });
});

backdrop?.addEventListener('click', () => setSidebarOpen(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebarOpen(false);
    }
});

document.querySelectorAll('[data-flash]').forEach((mensaje) => {
    const cerrar = () => {
        mensaje.classList.add('translate-x-4', 'opacity-0');
        setTimeout(() => mensaje.remove(), 300);
    };

    mensaje.querySelector('[data-flash-close]')?.addEventListener('click', cerrar);

    if (mensaje.dataset.flash === 'success') {
        setTimeout(cerrar, 6000);
    }
});

/*
 * Venta: la cantidad depende del producto (solo los productos con gramaje admiten
 * decimales), y en el alta se muestran el subtotal de cada línea y el total.
 */
const formatoDinero = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const lineasDeVenta = document.getElementById('venta-lineas');
const plantillaDeLinea = document.getElementById('venta-linea-plantilla');

const importeDeLinea = (linea) => {
    const opcion = linea.querySelector('[data-producto]').selectedOptions[0];
    const cantidad = parseFloat(linea.querySelector('[data-cantidad]').value);

    if (!opcion || opcion.value === '' || !Number.isFinite(cantidad)) {
        return 0;
    }

    return Math.round(parseFloat(opcion.dataset.precio) * cantidad * 100) / 100;
};

const actualizarTotalDeVenta = () => {
    const total = document.querySelector('[data-total-venta]');

    if (total) {
        const suma = [...document.querySelectorAll('[data-linea-venta]')]
            .reduce((acumulado, linea) => acumulado + importeDeLinea(linea), 0);

        total.textContent = formatoDinero.format(suma);
    }
};

const actualizarLineaDeVenta = (linea) => {
    const opcion = linea.querySelector('[data-producto]').selectedOptions[0];
    const cantidad = linea.querySelector('[data-cantidad]');
    const elegido = opcion !== undefined && opcion.value !== '';
    const conGramaje = elegido && opcion.dataset.gramaje === '1';

    cantidad.step = conGramaje ? '0.001' : '1';
    cantidad.min = conGramaje ? '0.001' : '1';

    linea.querySelector('[data-ayuda-cantidad]').textContent = !elegido
        ? 'Elige primero el producto'
        : conGramaje ? 'Producto con gramaje: admite hasta 3 decimales' : 'Solo cantidades enteras';

    const subtotal = linea.querySelector('[data-subtotal]');

    if (subtotal) {
        subtotal.textContent = formatoDinero.format(importeDeLinea(linea));
    }
};

const siguienteIndiceDeLinea = () => 1 + Math.max(-1, ...[...lineasDeVenta.querySelectorAll('[data-producto]')]
    .map((producto) => Number(producto.name.match(/^lineas\[(\d+)\]/)?.[1] ?? -1)));

const agregarLineaDeVenta = () => {
    lineasDeVenta.insertAdjacentHTML(
        'beforeend',
        plantillaDeLinea.innerHTML.replaceAll('__INDICE__', String(siguienteIndiceDeLinea())),
    );

    const linea = lineasDeVenta.lastElementChild;

    actualizarLineaDeVenta(linea);
    linea.querySelector('[data-producto]').focus();
};

document.querySelectorAll('[data-linea-venta]').forEach(actualizarLineaDeVenta);
actualizarTotalDeVenta();

['input', 'change'].forEach((evento) => {
    document.addEventListener(evento, (e) => {
        const linea = e.target.closest?.('[data-linea-venta]');

        if (linea) {
            actualizarLineaDeVenta(linea);
            actualizarTotalDeVenta();
        }
    });
});

document.addEventListener('click', (e) => {
    if (lineasDeVenta && e.target.closest('[data-agregar-linea]')) {
        agregarLineaDeVenta();
    }

    const quitar = e.target.closest('[data-quitar-linea]');

    if (lineasDeVenta && quitar) {
        quitar.closest('[data-linea-venta]').remove();

        // Una venta siempre tiene al menos una línea.
        if (!lineasDeVenta.querySelector('[data-linea-venta]')) {
            agregarLineaDeVenta();
        }

        actualizarTotalDeVenta();
    }
});
