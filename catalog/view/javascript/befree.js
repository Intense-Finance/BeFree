/* BeFree OpenCart Theme — GPLv3 — generated with Grok (xAI). See README.md. */
(function () {
  function read(id) {
    var el = document.getElementById(id);
    return el ? Number(el.value) : 0;
  }
  function paint() {
    var annual = document.getElementById('pn-annual');
    if (!annual) return;
    var trucks = read('pn-trucks') || 12;
    var gallons = read('pn-gallons') || 8;
    var services = read('pn-services') || 4;
    var total = trucks * gallons * services;
    annual.textContent = total.toLocaleString();
    var advice = document.getElementById('pn-advice');
    if (!advice) return;
    var pack = total > 800 ? 'a bulk tote' : (total > 200 ? 'a drum' : 'case quantity');
    advice.textContent = 'About ' + total.toLocaleString() + ' units a year. Start the quote at ' + pack + ', then confirm the real pack size on the product label.';
  }
  document.addEventListener('input', function (event) {
    if (event.target && event.target.closest && event.target.closest('#pn-advisor')) paint();
  });
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', paint);
  } else {
    paint();
  }
})();
