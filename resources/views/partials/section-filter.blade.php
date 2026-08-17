<script>
(function () {
    const ay = document.querySelector('select[name="academic_year_id"]');
    const sectionSelects = document.querySelectorAll('[data-section-filter]');
    if (!ay || !sectionSelects.length) return;

    const allOptions = new Map();
    sectionSelects.forEach(sel => {
        allOptions.set(sel, Array.from(sel.options).map(o => ({
            value: o.value,
            text: o.text,
            ay: o.getAttribute('data-ay')
        })));
    });

    function apply() {
        const val = ay.value;
        sectionSelects.forEach(sel => {
            const opts = allOptions.get(sel);
            sel.innerHTML = '';
            opts.forEach(o => {
                if (!val || o.ay === val) {
                    const opt = new Option(o.text, o.value);
                    opt.setAttribute('data-ay', o.ay);
                    sel.add(opt);
                }
            });
        });
    }

    ay.addEventListener('change', apply);
    apply();
})();
</script>
