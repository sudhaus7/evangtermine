
const checkboxes =  document.querySelectorAll('[type="checkbox"]');
for (let i = 0; i < checkboxes.length; i++) {
  if (checkboxes[i].id.includes('toggleAllFilters')) {
    checkboxes[i].addEventListener('change', function () {
      const tabPane = this.closest('.tab-pane');
      const checkboxesOfCurrentPane = tabPane.querySelectorAll('[type="checkbox"]');
      if (this.checked) {
        for (let j = 0; j < checkboxesOfCurrentPane.length; j++) {
          if (!checkboxesOfCurrentPane[j].checked) {
            checkboxesOfCurrentPane[j].click();
          }
        }
      } else {
        for (let j = 0; j < checkboxesOfCurrentPane.length; j++) {
          if (checkboxesOfCurrentPane[j].checked) {
            checkboxesOfCurrentPane[j].click();
          }
        }
      }
    });
  }
}
