jQuery(function ($) {
    // Playlist table click event
    const trackInfo = $('#track-info')

    function get_fb_url(fb_id) {
        const match = fb_id.split('_', 2)

        return `https://www.facebook.com/${match[0]}/posts/${match[1]}`;
    }

    function get_yt_url(yt_id) {
        const v = encodeURIComponent(yt_id)

        return `https://youtube.com/watch?v=${v}`
    }

    const playlistId = $('#dialog-id'),
        fbLink = $('#dialog-fb-link'),
        ytLink = $('#dialog-yt-link'),
        artist = $('#dialog-artist'),
        title = $('#dialog-title'),
        length = $('#dialog-length'),
        rating = $('#dialog-rating'),
        createdAt = $('#dialog-created_at'),
        updatedAt = $('#dialog-updated_at'),
        description = $('#dialog-description')

    $('#playlist-table > .table-body > .row').on('click', (e) => {
        e.preventDefault()

        const row = $(e.currentTarget).data('row')
        console.log(row)

        let fb = '',
            yt = ''

        if (row.fb_id) {
            fb = $('<a>', {href: get_fb_url(row.fb_id), target: '_blank'})
            fb.append('Visit')
        } else {
            fb = 'No Post ID'
        }

        if (row.yt_id) {
            yt = $('<a>', {href: get_yt_url(row.yt_id), target: '_blank'})
            yt.append('Listen')
        } else {
            yt = 'No Watch ID'
        }

        playlistId.text(row.id)
        fbLink.html(fb)
        ytLink.html(yt)
        artist.text(row.artist)
        title.text(row.title)
        length.text(row.length)
        rating.text(row.rating ?? 'Not rated')
        createdAt.text(row.created_time)
        updatedAt.text(row.created_time !== row.updated_time ? row.updated_time : 'No Update')
        description.html(row.description)

        trackInfo[0].showModal()
        trackInfo.scrollTop(0)
    })

    $('.close', trackInfo).on('click', () => {
        trackInfo[0].close()
    })

    // Clear keyword.
    $('#clear').on('click', () => {
        $('#keyword').val('')
    })

    // Page enter event.
    $('.pagination .page').on('keyup', (e) => {
        if ('Enter' === e.key) {
            const page = parseInt(e.target.value)
            if (isNaN(page)) {
                return
            }

            const params = new URLSearchParams(window.location.search)
            params.set('pg', page.toString())

            location.href = '?' + params.toString()
        }
    })
});
