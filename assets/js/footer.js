document.addEventListener('DOMContentLoaded', () => {
  const dateEl = document.getElementById('DateTime');
  const locale = 'fa-IR';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  
  function updateDateTime() {
    const now = new Date();
    const date = new Intl.DateTimeFormat(locale, options).format(now);
    const time = now.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' });
    dateEl.textContent = `امروز ${date} ساعت ${time}`;
  }

  updateDateTime();
  dateEl.addEventListener('click', () => window.location.href = './calendar.php');
  setInterval(updateDateTime, 10000);
});
