(function(){
  // Accent sync
  if (window.PluginHub && PluginHub.accent){
    document.documentElement.style.setProperty('--ph-accent', PluginHub.accent);
  }

  // Tabs
  document.addEventListener('click', function(e){
    if(e.target.matches('.ph-tabs-nav button')){
      var btn = e.target;
      var wrap = btn.closest('.ph-tabs');
      if(!wrap) return;
      wrap.querySelectorAll('.ph-tabs-nav button').forEach(function(b){ b.classList.remove('is-active'); });
      wrap.querySelectorAll('.ph-tabs-content').forEach(function(c){ c.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var id = btn.getAttribute('data-tab');
      var content = wrap.querySelector('#'+id);
      if(content){ content.classList.add('is-active'); }
    }
  });

  // AJAX search / filter
  var form = document.getElementById('ph-filter-form');
  if(!form || !window.PluginHub) return;

  var searchInput = document.getElementById('ph-search-input');
  var catSelect   = document.getElementById('ph-category-filter');
  var resultsWrap = document.getElementById('ph-results');

  var timer = null;
  function triggerSearch(){
    if(!resultsWrap) return;
    var s   = searchInput ? searchInput.value : '';
    var cat = catSelect ? catSelect.value : '';

    var data = new FormData();
    data.append('action','ph_search_plugins');
    data.append('s', s);
    data.append('category', cat);

    fetch(PluginHub.ajax_url, {
      method:'POST',
      credentials:'same-origin',
      body:data
    }).then(function(r){return r.json();})
    .then(function(json){
      if(json && json.success && json.data && typeof json.data.html === 'string'){
        resultsWrap.innerHTML = json.data.html;
      }
    }).catch(function(err){
      console.error('PluginHub AJAX error', err);
    });
  }

  function debounceSearch(){
    clearTimeout(timer);
    timer = setTimeout(triggerSearch, 300);
  }

  if(searchInput){
    searchInput.addEventListener('input', debounceSearch);
  }
  if(catSelect){
    catSelect.addEventListener('change', triggerSearch);
  }
})();
