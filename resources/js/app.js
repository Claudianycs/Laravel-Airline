import 'bootstrap';
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelector('#print-ticket')?.addEventListener('click', () => window.print());
