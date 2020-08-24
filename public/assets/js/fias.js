'use strict';

/**
 * 24.08.2020
 * File: fias.js
 * Encoding: UTF-8
 * Author: MaXL
 * */

(function (document) {
  const EVENT_DOM_ELEMENT_CHANGE = 'change';

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
  
  const enableSelect = function (element) {
    element.removeAttribute('disabled');
  };

  const disableSelect = function (element) {
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
  
  const getJson = function (url, callback) {
    const httpRequest = new XMLHttpRequest();

    httpRequest.onreadystatechange = function () {
      if (httpRequest.readyState === XMLHttpRequest.DONE) {
        callback(JSON.parse(httpRequest.responseText));
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
    
    // events
    regionEl.addEventListener(
      EVENT_DOM_ELEMENT_CHANGE,
      function () {
        _this.searchValues.regionId = getSelectedValue(regionEl);
        _this.searchValues.cityId = null;
        _this.searchValues.streetId = null;
        _this.searchValues.houseId = null;
        
        // clear and disabled depends selects
        disableSelect(cityEl);
        disableSelect(streetEl);
        clearSelect(cityEl);
        clearSelect(streetEl);
        
        if (_this.searchValues.regionId) {
          loadCities(
            function () {
              enableSelect(cityEl);
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
        disableSelect(streetEl);
        clearSelect(streetEl);
        
        if (_this.searchValues.cityId) {
          loadStreets(
            function () {
              enableSelect(streetEl);
            }
          );
        }
      }
    );
  };

  let sf = new searchForm();
  
})(document);
