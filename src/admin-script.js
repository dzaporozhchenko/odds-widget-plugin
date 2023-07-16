(function( $ ) {
	$('#group').on('change', function() {
		$('#empty_group_option').remove()
		const group = $('#group').find(":selected").text()
		let newSports = data.all_sports.filter(s => s.group === group);

		const sportElement = $('#sport_key')
		sportElement.empty()
		sportElement.prop( "disabled", false )
		sportElement.append($('<option id="empty-sport-option"></option>'))
		$.each(newSports, function(key,sport) {
			sportElement.append($("<option></option>").attr("value", sport.key).text(sport.title));
		});

		const gameElement = $('#game_id')
		gameElement.empty()
		gameElement.prop( "disabled", true )
		gameElement.append($('<option id="empty-game-option"></option>'))

		$('#bookmakers-table-body').empty()
	});

	$('#sport_key').on('change', function() {
		$('#empty-sport-option').remove()
		const sportKey = $('#sport_key').val()
		const sport = data.all_sports.find(s => s.key === sportKey)
		$('#sport_key_description').empty().text(sport.description)

		let newGames = wp.apiFetch({path: wp.url.addQueryArgs('/odds-widget/odds', {sport: sportKey})}).then((newGames) => {
			data.all_odds = newGames.reduce((acc, curr) => (acc[curr.id] = curr,acc), {});
			const gameElement = $('#game_id')
			gameElement.empty()
			gameElement.prop( "disabled", false )
			gameElement.append($('<option id="empty-game-option"></option>'))
			$.each(newGames, function(key,game) {
				gameElement.append(
					$("<option></option>")
						.attr("value", game.id)
						.text(game.commence_time.split("T", 1)[0] + ': ' + game.home_team + ' - ' + game.away_team)
				)
			});
		} );

		$('#bookmakers-table-body').empty()
	});

	$('#game_id').on('change', function() {
		$('#empty-game-option').remove()
		const gameId = $('#game_id').val()
		const game = data.all_odds[gameId]
		const bookmakers = game.bookmakers.map(b => b.key)

		const bookmakersTableBody = $('#bookmakers-table-body')
		bookmakersTableBody.empty()
		$.each(game.bookmakers, function(key,bookmaker) {
			const column1 = $('<td></td>')
			column1.append(
				$('<input/>').attr({
					type: 'checkbox',
					name: `bookmakers[${bookmaker.key}]`,
					id: `bookmakers[${bookmaker.key}]`,
					value: 1,
					checked: data.current_bookmakers.includes(bookmaker.key)

				})
			)
			const column2 = $('<td></td>')
			column2.append($(`<label for="bookmakers[${bookmaker.key}]">${bookmaker.title}</label>`))

			const column3 = $('<td></td>')
			column3.append(
				$('<input/>').attr({
					type: 'url',
					name: `bookmakers_url[${bookmaker.key}]`,
					id: `bookmakers_url[${bookmaker.key}]`,
					value: data.bookmakers_url_settings[bookmaker.key] ?? '',
					autoComplete: "off",
					placeholder: wp.i18n.__('Set partner link', 'odds-widget'),
				})
			)
			const row = $('<tr></tr>')
			row.append(column1)
			row.append(column2)
			row.append(column3)
			bookmakersTableBody.append(row)
		});
	});
})(jQuery);

function refreshBookmakersTable(sport) {
	$('#bookmakers-table-body').append('<p>Azaza</p>')
}

