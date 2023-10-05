jQuery(function ($) {
    const table = $('.playlist-table'),
        tbody = $('tbody', table)

    $('tr.row', tbody).on('click', function (e) {
        e.preventDefault()

        const row = $(e.currentTarget),
            id = row.data('id')

        if (!id) {
            return
        }

        row.siblings(`.data-row[data-id="${id}"]`).toggleClass('hidden')
    })
});