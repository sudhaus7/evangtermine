const evangTermineResetCookieValue = function () {
  let cookiesArray = document.cookie.split(';');
  for (let i = 0; i < cookiesArray.length; i++) {
    if (cookiesArray[i].trim().startsWith('etpluginuid')) {
      let cookieArray = cookiesArray[i].split('=');
      document.cookie = cookieArray[0] + '=-1; max-age=10; SameSite=None; Secure';
    }
  }
}

const evangTerminePluginUid = function () {
  evangTermineResetCookieValue();

  const links = document.querySelectorAll('.plugin-evangelische-termine a');
  for (let i = 0; i < links.length; i++) {
    links[i].addEventListener('click', function () {
      const pluginUid = this.dataset.pluginuid;
      document.cookie = 'etpluginuid' + pluginUid + '=' + pluginUid + '; max-age=10; SameSite=None; Secure';
    });
  }
}
evangTerminePluginUid();
