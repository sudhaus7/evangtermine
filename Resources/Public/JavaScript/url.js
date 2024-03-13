const defaultValues = {
  highlight: 'all',
  eventtype: 'all',
  people: '0',
  region: 'all',
  place: 'all',
  zip: '',
  radius: '0',
  q: '',
  date: '',
  hideOngoingEvents: '0',
  itemsPerPage: '20',
  pageId: '1'
};

const replaceUrl = function () {
  const forms = document.getElementsByName('etkeysForm');

  if (forms.length === 1) {
    forms.forEach(function (form) {
      const formData = new FormData(form);
      const urlChangeOption = getUrlChangeOption(formData.entries(), defaultValues);
      const urlPartToKeep = removeProtocolAndHostFromUrl();

      switch (urlChangeOption) {
        case 'highlightEventtypePeople':
          urlForHighlightEventtypePeople(formData.entries(), urlPartToKeep);
          break;
        case 'region':
          urlForRegion(formData.entries(), urlPartToKeep);
          break;
        case 'place':
          urlForPlace(formData.entries(), urlPartToKeep);
          break;
      }
    });
  }
};

const getChangedOptions = function(entries, defaultValues) {
  let changed = {
    highlight: false,
    eventtype: false,
    people: false,
    region: false,
    place: false,
    zip: false,
    radius: false,
    q: false,
    date: false,
    hideOngoingEvents: false,
    itemsPerPage: false
  }
  for (let pair of entries) {
    for (const [key, value] of Object.entries(defaultValues)) {
      if (pair[0] == 'tx_evangtermine_list[etkeysForm][' + key + ']') {
        if (pair[1] != value) {
          if (key === 'highlight') {
            changed.highlight = true;
          }
          if (key === 'eventtype') {
            changed.eventtype = true;
          }
          if (key === 'people') {
            changed.people = true;
          }
          if (key === 'region') {
            changed.region = true;
          }
          if (key === 'place') {
            changed.place = true;
          }
          if (key === 'zip') {
            changed.zip = true;
          }
          if (key === 'radius') {
            changed.radius = true;
          }
          if (key === 'q') {
            changed.q = true;
          }
          if (key === 'date') {
            changed.date = true;
          }
          if (key === 'hideOngoingEvents') {
            changed.hideOngoingEvents = true;
          }
          if (key === 'itemsPerPage') {
            changed.itemsPerPage = true;
          }
        }
      }
    }
  }
  return changed;
}

const getUrlChangeOption = function (entries, defaultValues) {
  let changed = getChangedOptions(entries, defaultValues);
  if (changed.zip || changed.radius || changed.q || changed.date || changed.hideOngoingEvents || changed.itemsPerPage) {
    return 'none';
  }
  if (changed.eventtype && changed.people && !changed.region && !changed.place) {
    return 'highlightEventtypePeople';
  }
  if (!changed.highlight && !changed.eventtype && !changed.people && changed.region && !changed.place) {
    return 'region';
  }
  if (!changed.highlight && !changed.eventtype && !changed.people && !changed.region && changed.place) {
    return 'place';
  }
};

const removeProtocolAndHostFromUrl = function () {
  const urlArray = window.location.href.split(window.location.host);
  return urlArray[1].replace('.html', '');
};

const urlForHighlightEventtypePeople = function (entries, urlPartToKeep) {
  const urlPartToKeepArray = urlPartToKeep.split('/highlight/');
  urlPartToKeep = urlPartToKeepArray[0];

  let highlight, eventType, people;
  for (let pair of entries) {
    if (pair[0] === 'tx_evangtermine_list[etkeysForm][highlight]') {
      highlight = pair[1];
    }
    if (pair[0] === 'tx_evangtermine_list[etkeysForm][eventtype]') {
      eventType = pair[1];
    }
    if (pair[0] === 'tx_evangtermine_list[etkeysForm][people]') {
      people = pair[1];
    }
  }

  let url = urlPartToKeep + '/highlight/' + highlight + '/kategorie/' + eventType + '/zielgruppe/' + people;
  url.replace('//', '/');
  history.pushState('', '', url);
};

const urlForRegion = function (entries, urlPartToKeep) {
  const urlPartToKeepArray = urlPartToKeep.split('/region/');
  urlPartToKeep = urlPartToKeepArray[0];

  let region;
  for (let pair of entries) {
    if (pair[0] === 'tx_evangtermine_list[etkeysForm][region]') {
      region = pair[1];
    }
  }

  region = changeString(region);
  region = changeString(region);
  region = changeString(region);
  region = changeString(region);
  region = changeString(region);

  let url = urlPartToKeep + '/region/' + region;
  url.replace('//', '/');
  history.pushState('', '', url);
};

const urlForPlace = function (entries, urlPartToKeep) {
  const urlPartToKeepArray = urlPartToKeep.split('/ort/');
  urlPartToKeep = urlPartToKeepArray[0];

  let place;
  let placeName = '';
  for (let pair of entries) {
    if (pair[0] === 'tx_evangtermine_list[etkeysForm][place]') {
      place = pair[1];
      placeName = document.querySelector('#places option[value="' + place + '"]').text;
    }
  }
  placeName = changeString(placeName);
  placeName = changeString(placeName);
  placeName = changeString(placeName);
  placeName = changeString(placeName);
  placeName = changeString(placeName);

  let url = urlPartToKeep + '/ort/' + placeName;
  url.replace('//', '/');
  history.pushState('', '', url);
};

const changeString = function (string) {
  string = string.replace(' ', '-');
  string = string.replace('--', '');
  string = string.replace('ß', 'ss');
  string = string.replace('"', '');
  string = string.replace("'", '');
  string = string.replace('.', '');
  string = string.replace(':', '');
  string = string.replace('?', '');
  string = string.replace('!', '');
  string = string.replace('(', '');
  string = string.replace(')', '');
  string = string.toLowerCase();
  string = string.replace('ä', 'ae');
  string = string.replace('ö', 'oe');
  return string.replace(/(?:\r\n|\r|\n)/g, '');
};

replaceUrl();

const scrollToResults = function () {
  const forms = document.getElementsByName('etkeysForm');
  for (let i = 0; i < forms.length; i++) {
    const form = forms[i];

    form.addEventListener('submit', function () {
      localStorage.setItem('evangtermine-form', 'submitted');
    });
    const paginationItems = form.querySelectorAll('.pagination li a');
    for (let j = 0; j < paginationItems.length; j++) {
      paginationItems[j].addEventListener('click', function () {
        console.log('pewdfk')
        localStorage.setItem('evangtermine-form', 'submitted');
      });
    }

    let storage = '';
    try {
      storage = localStorage.getItem('evangtermine-form');
    } catch (e) {

    }
    if (storage === 'submitted') {
      let offsetTop = form.querySelector('.et-event').offsetTop;
      if (undefined !== offsetTop) {
        const fixedHeader = document.querySelector('header.fixed-header');
        if (fixedHeader) {
          const topToolbar = document.querySelector('.top-toolbar');
          let topToolbarHeight = 0;
          if (topToolbar) {
            topToolbarHeight = topToolbar.offsetHeight;
          }
          const mainNavigationToolbar = document.querySelector('.main-navigation-toolbar');
          let mainNavigationToolbarHeight = 0;
          if (mainNavigationToolbar) {
            mainNavigationToolbarHeight = mainNavigationToolbar.offsetHeight;
          }
          const main = document.querySelector('main');
          const mainOffset = main.offsetTop;
          offsetTop = offsetTop - mainOffset - topToolbarHeight - mainNavigationToolbarHeight;
        }
        setTimeout(function () {
          window.scrollTo(0, offsetTop);
        }, 300);
      }
      localStorage.removeItem('evangtermine-form');
    }
  }
}
scrollToResults();
