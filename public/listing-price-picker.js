{
    const picker = document.querySelector('[data-price-picker]');
    const select = picker?.querySelector('[data-currency-select]');

    if (select) {
        const sale = picker.querySelector('[data-sale-price]');
        const original = picker.querySelector('[data-original-price]');

        function updatePrice() {
            const option = select.selectedOptions[0];
            sale.textContent = option.dataset.sale;
            if (original) original.textContent = option.dataset.original;
        }

        select.addEventListener('change', updatePrice);
        window.addEventListener('pageshow', updatePrice);
        picker.querySelector('[data-currency-field]').hidden = false;
        updatePrice();
    }
}
