/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************!*\
  !*** ./src/admin-script.js ***!
  \*****************************/
(function ($) {
  $('#group').on('change', function () {
    $('#empty_group_option').remove();
    var group = $('#group').find(":selected").text();
    var newSports = data.all_sports.filter(function (s) {
      return s.group === group;
    });
    var sportElement = $('#sport_key');
    sportElement.empty();
    sportElement.prop("disabled", false);
    sportElement.append($('<option id="empty-sport-option"></option>'));
    $.each(newSports, function (key, sport) {
      sportElement.append($("<option></option>").attr("value", sport.key).text(sport.title));
    });
    var gameElement = $('#game_id');
    gameElement.empty();
    gameElement.prop("disabled", true);
    gameElement.append($('<option id="empty-game-option"></option>'));
    $('#bookmakers-table-body').empty();
  });
  $('#sport_key').on('change', function () {
    $('#empty-sport-option').remove();
    var sportKey = $('#sport_key').val();
    var sport = data.all_sports.find(function (s) {
      return s.key === sportKey;
    });
    $('#sport_key_description').empty().text(sport.description);
    var newGames = wp.apiFetch({
      path: wp.url.addQueryArgs('/odds-widget/odds', {
        sport: sportKey
      })
    }).then(function (newGames) {
      data.all_odds = newGames.reduce(function (acc, curr) {
        return acc[curr.id] = curr, acc;
      }, {});
      var gameElement = $('#game_id');
      gameElement.empty();
      gameElement.prop("disabled", false);
      gameElement.append($('<option id="empty-game-option"></option>'));
      $.each(newGames, function (key, game) {
        gameElement.append($("<option></option>").attr("value", game.id).text(game.commence_time.split("T", 1)[0] + ': ' + game.home_team + ' - ' + game.away_team));
      });
    });
    $('#bookmakers-table-body').empty();
  });
  $('#game_id').on('change', function () {
    $('#empty-game-option').remove();
    var gameId = $('#game_id').val();
    var game = data.all_odds[gameId];
    var bookmakers = game.bookmakers.map(function (b) {
      return b.key;
    });
    var bookmakersTableBody = $('#bookmakers-table-body');
    bookmakersTableBody.empty();
    $.each(game.bookmakers, function (key, bookmaker) {
      var _data$bookmakers_url_;
      var column1 = $('<td></td>');
      column1.append($('<input/>').attr({
        type: 'checkbox',
        name: "bookmakers[".concat(bookmaker.key, "]"),
        id: "bookmakers[".concat(bookmaker.key, "]"),
        value: 1,
        checked: data.current_bookmakers.includes(bookmaker.key)
      }));
      var column2 = $('<td></td>');
      column2.append($("<label for=\"bookmakers[".concat(bookmaker.key, "]\">").concat(bookmaker.title, "</label>")));
      var column3 = $('<td></td>');
      column3.append($('<input/>').attr({
        type: 'url',
        name: "bookmakers_url[".concat(bookmaker.key, "]"),
        id: "bookmakers_url[".concat(bookmaker.key, "]"),
        value: (_data$bookmakers_url_ = data.bookmakers_url_settings[bookmaker.key]) !== null && _data$bookmakers_url_ !== void 0 ? _data$bookmakers_url_ : '',
        autoComplete: "off",
        placeholder: wp.i18n.__('Set partner link', 'odds-widget')
      }));
      var row = $('<tr></tr>');
      row.append(column1);
      row.append(column2);
      row.append(column3);
      bookmakersTableBody.append(row);
    });
  });
})(jQuery);
function refreshBookmakersTable(sport) {
  $('#bookmakers-table-body').append('<p>Azaza</p>');
}
/******/ })()
;
//# sourceMappingURL=admin-script.js.map