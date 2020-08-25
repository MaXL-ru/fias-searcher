'use strict';

/**
 * 24.08.2020
 * File: fias.js
 * Encoding: UTF-8
 * Author: MaXL
 * */

(function (document) {
  const EVENT_DOM_ELEMENT_CHANGE = 'change';
  const EVENT_DOM_ELEMENT_CLICK  = 'click';
  
  const ERR_WHILE_LOAD_DATA = 'Произошла ошибка во время загрузки данных: ';

  // helpers
  const getElementById = function (id) {
    return document.getElementById(id);
  };
  
  const getSelectedValue = function (element) {
    return element.options[element.selectedIndex].value;
  };
  
  const clearSelect = function (element) {
    for (let i = 0, l = element.length; i < l; ++i) {
      element.remove(0);
    }
  };
  
  const enableElement = function (element) {
    element.removeAttribute('disabled');
  };

  const disableElement = function (element) {
    element.setAttribute('disabled', true);
  };
  
  const assignItemsToSelect = function (element, items) {
    // add empty value
    let emptyOption = document.createElement('option'); 
    element.add(emptyOption);
    
    items.forEach(
      (i) => {
        const option = document.createElement('option');
        
        option.value = i.value;
        option.text = i.text;
        
        element.add(option);
      }
    );
  };
  
  const assignItemsToTable = function (element, items) {
    items.forEach(
      (row) => {
        const tr = document.createElement('tr');

        row.forEach(
          (cell) => {
            const td = document.createElement('td');
            
            td.innerHTML = cell;
            
            tr.appendChild(td);
          }
        );
        
        element.appendChild(tr);
      }
    );
  };
  
  const getJson = function (url, callback) {
    const httpRequest = new XMLHttpRequest();

    httpRequest.onreadystatechange = function () {
      if (httpRequest.readyState === XMLHttpRequest.DONE) {
        let result = null;
        
        try {
          result = JSON.parse(httpRequest.responseText);
        } finally {
          if (result === null || result.error !== undefined) {
            alert(ERR_WHILE_LOAD_DATA + (result.error || ''));
          }
          
          callback(result || []);
        }
      }
    };
    
    httpRequest.open('GET', url, true);
    
    httpRequest.send();
  };
  
  const searchForm = function () {
    let _this = this;
    
    // search elements
    const regionEl = getElementById('search_fias_region_id');
    const cityEl   = getElementById('search_fias_city_id');
    const streetEl = getElementById('search_fias_street_id');
    const houseEl  = getElementById('search_fias_house_id');
    
    const searchBtn = getElementById('search_fias_search_id');

    const searchResultContainer = getElementById('fias__search__result_id');
    const searchResults = getElementById('search_fias_result_tbl_id');
    
    // selected values
    _this.searchValues = {
      regionId: null,
      cityId  : null,
      streetId: null,
      houseId : null
    };
    
    // data providers
    const loadCities = function (callback) {
      getJson(
        '/index.php?action=cities&regionGuid=' + _this.searchValues.regionId,
        function (cities) {
          assignItemsToSelect(cityEl, cities);
          
          callback();
        }
      );
    };
    
    const loadStreets = function (callback) {
      getJson(
        '/index.php?action=streets&cityGuid=' + _this.searchValues.cityId,
        function (streets) {
          assignItemsToSelect(streetEl, streets);
          
          callback();
        }
      );
    };
    
    const loadHouses = function (callback) {
      getJson(
        '/index.php?action=houses&streetGuid=' + _this.searchValues.streetId,
        function (houses) {
          assignItemsToSelect(houseEl, houses);
          
          callback();
        }
      );
    };
    
    const search = function (callback) {
      getJson(
        '/index.php?action=search'             +
          '&regionGuid=' + _this.searchValues.regionId +
          '&cityGuid='   + _this.searchValues.cityId   +
          '&streetGuid=' + _this.searchValues.streetId +
          '&houseGuid='  + (_this.searchValues.houseId || ''),
        function (addresses) {
          const tBodyResult = searchResults.getElementsByTagName('tbody')[0];
          
          tBodyResult.innerHTML = '';
          assignItemsToTable(tBodyResult, addresses);

          searchResultContainer.setAttribute(
            'data-is-found',
            addresses.length > 0 ? '1' : '0'
          );
          
          callback();
        }
      );
    };
    
    // helpers
    // clear and disable select
    const resetSelects = function (elements) {
      elements.forEach(
        (selectEl) => { disableElement(selectEl); clearSelect(selectEl); }
      );
    };
    
    // events
    regionEl.addEventListener(
      EVENT_DOM_ELEMENT_CHANGE,
      function () {
        _this.searchValues.regionId = getSelectedValue(regionEl);
        _this.searchValues.cityId = null;
        _this.searchValues.streetId = null;
        _this.searchValues.houseId = null;
        
        // clear and disabled depends selects
        resetSelects([cityEl, streetEl, houseEl]);

        // disable searching
        disableElement(searchBtn);
        
        if (_this.searchValues.regionId) {
          loadCities(
            function () {
              enableElement(cityEl);
            }
          );
        }
      }
    );
    
    cityEl.addEventListener(
      EVENT_DOM_ELEMENT_CHANGE,
      function () {
        _this.searchValues.cityId = getSelectedValue(cityEl);
        _this.searchValues.streetId = null;
        _this.searchValues.houseId = null;

        // clear and disable depends selects
        resetSelects([streetEl, houseEl]);

        // disable searching
        disableElement(searchBtn);
        
        if (_this.searchValues.cityId) {
          loadStreets(
            function () {
              enableElement(streetEl);
            }
          );
        }
      }
    );
    
    streetEl.addEventListener(
      EVENT_DOM_ELEMENT_CHANGE,
      function () {
        _this.searchValues.streetId = getSelectedValue(streetEl);
        
        // clear and disable depends selects
        resetSelects([houseEl]);
        
        if (_this.searchValues.streetId) {
          loadHouses(
            function () {
              enableElement(houseEl);
              
              enableElement(searchBtn);
            }
          );
        } else {
          disableElement(searchBtn);
        }
      }
    );

    houseEl.addEventListener(
      EVENT_DOM_ELEMENT_CHANGE,
      function () {
        _this.searchValues.houseId = getSelectedValue(houseEl);
      }
    );

    searchBtn.addEventListener(
      EVENT_DOM_ELEMENT_CLICK,
      function () {
        disableElement(searchBtn);
        
        search(() => { enableElement(searchBtn) });
      }
    )
  };
  
  let sf = new searchForm();
  
})(document);
