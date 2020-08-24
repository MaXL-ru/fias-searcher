<?php

/* @var $regions string[] */

?><!DOCTYPE html><html lang="ru-RU">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Поиск данных ФИАС</title>
    <link href="/assets/css/fias.css" rel="stylesheet">
  </head>
  <body>
    <h1>Поиск данных в ФИАС</h1>
    
    <form action="/index.php?action=search" method="post"
      class="fias__searchform"
    >
      <h3>Уточните критерии поиска</h3>
      
      <div class="fias__searchform__row">
        <div class="fias__searchform__region">
          <label for="search_fias_region_id">Регион / Область / АО</label>
          <select name="region" autocomplete="off" id="search_fias_region_id">
            <option value=""></option>
            <?php foreach ($regions as $region): ?>
              <option value="<?= $region['value'] ?>">
                <?= htmlspecialchars($region['text'], ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
  
        <div class="fias__searchform__city">
          <label for="search_fias_city_id">Город</label>
          <select name="region" autocomplete="off" id="search_fias_city_id"
            disabled
          >
            
          </select>
        </div>
      </div>

      <div class="fias__searchform__row">
        <div class="fias__searchform__street">
          <label for="search_fias_street_id">Улица</label>
          <select name="region" autocomplete="off" id="search_fias_street_id"
            disabled
          >
            
          </select>
        </div>
  
        <div class="fias__searchform__house">
          <label for="search_fias_house_id">Дом</label>
          <select name="region" autocomplete="off" id="search_fias_house_id"
            disabled
          >
            
          </select>
        </div>
      </div>
      
      <div class="buttons">
        <button type="submit" class="fias__searchform__btn__search">
          Поиск
        </button>
      </div>
    </form>
    
    <footer>
      © MaXL 2020
    </footer>
    <script src="assets/js/fias.js"></script>
  </body>
</html>
