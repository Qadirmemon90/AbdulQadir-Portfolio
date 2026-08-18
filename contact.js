document.querySelectorAll('.contact-form').forEach(form => {
  form.addEventListener('submit', async event => {
    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');
    const status = form.querySelector('.contact-status');
    const originalLabel = button.textContent;

    button.disabled = true;
    button.textContent = 'Sending...';
    status.textContent = '';
    status.className = 'contact-status';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || 'Your message could not be sent.');
      }

      form.reset();
      status.textContent = result.message;
      status.classList.add('is-success');
    } catch (error) {
      status.textContent = error.message || 'Something went wrong. Please try again.';
      status.classList.add('is-error');
    } finally {
      button.disabled = false;
      button.textContent = originalLabel;
    }
  });
});
