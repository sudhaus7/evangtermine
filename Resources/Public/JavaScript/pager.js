const pager = function () {
  const pages = document.querySelectorAll('.tx-evangtermine .et_pager_container a');
  pages.forEach((page) => {
    page.addEventListener('click', function (e) {
      e.preventDefault();
      const form = this.closest('form');
      form.querySelector('.pageid').value = this.getAttribute('data-page');
      form.submit();
    });
  });
}
pager();
