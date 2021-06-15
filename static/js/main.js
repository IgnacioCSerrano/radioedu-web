$(function() {

    // BOOTSTRAP CSS LINK FALLBACK

    if ($('#bootstrapCssTest').is(':visible')) {
        $('head link:last').before('<link rel="stylesheet" href="./static/bootstrap-4.6.0-dist/css/bootstrap.min.css">')
    }

    // LECTURA DE IMAGEN DE PERFIL

    $('#imgProfileInput').on('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0]
            if (!file.type || file.type.indexOf('image') === -1) {
                alert('¡Fichero no es una imagen!')
                return
            }
            
            const reader = new FileReader()
            reader.onload = function(e) {
                $('#imgProfile').attr('src', e.target.result)
            }
            reader.readAsDataURL(file)
        }
    })

    // LECTURA DE IMAGEN DE RADIO Y PODCAST

    $('#imgInput').on('change', function() {
        readImage(this)
    })

    $('#btnResetImg').on('click', function() {
        resetFileInput($(this), $('#imgInput').get(0))
    })

    // LECTURA DE AUDIO DE PODCAST

    $('#audioInput').on('change', function() {
        readAudio(this)
    })

    // LISTA DESPEGABLE PARA ELEGIR CENTRO EDUCATIVO

    $('#provinciaSelectCreate').on('change', function() {
        loadSelect(['localidadSelectCreate', 'centroSelectCreate'], 
            $(this).attr('name'), $(this).val(), 'localidad', 'localidad')
    })

    $('#localidadSelectCreate').on('change', function() {
        loadSelect(['centroSelectCreate'], 
            $(this).attr('name'), $(this).val(), 'codigo', 'denominacion')
    })

    $('#provinciaSelectUpdate').on('change', function() {
        loadSelect(['localidadSelectUpdate', 'centroSelectUpdate'], 
            $(this).attr('name'), $(this).val(), 'localidad', 'localidad')
    })

    $('#localidadSelectUpdate').on('change', function() {
        loadSelect(['centroSelectUpdate'], 
            $(this).attr('name'), $(this).val(), 'codigo', 'denominacion')
    })

    // LISTA DESPEGABLE PARA FILTRAR ENTRADAS DE RADIO POR FECHA

    $('#entries').on('change', function() {
        window.location = $(this).val()
    })

    // PROCESO DE BLOQUEO DE COMENTARIOS EN PODCAST

    $('#checkBlockPod').change(function() {
        $('#blockPodForm').trigger('submit')
    })

    // BORRADO DE RADIO

    $(".modalRadio").click(function() {
        let id = $(this).data('id')         // dataset
        $("#idRadio").val(id)
        let nombre = $(this).data('name')   // dataset
        $('#modalMessage').text('¿Está seguro de que desea eliminar a ' + nombre + '?')
    })

    // BORRADO DE PODCAST

    $(".modalPodcast").click(function() {
        let id = $(this).data('id') // dataset
        $("#idPodcast").val(id)
        $('#modalMessage').text('¿Está seguro de que desea eliminar esta entrada de podcast?')
    })

    // BORRADO DE COMENTARIO

    $(".modalComment").click(function() {
        let id = $(this).data('id') // dataset
        $("#idComment").val(id)
        $('#modalMessage').text('¿Está seguro de que desea eliminar este comentario?')
    })

    $("#deleteItem").click(function() {
        $('#deleteForm').trigger('submit')
    })

    // REPRODUCTOR PERSONALIZADO DE AUDIO (PODCAST)

    if ( $('#pcast-player').length ) {
        let speeds = [1, 1.25, 1.5, 1.75, 2]
        let currentSpeedIndex = 0

        let audio = document.querySelector('audio')
        let play = document.querySelector('.pcast-play')
        let pause = document.querySelector('.pcast-pause')
        let rewind = document.querySelector('.pcast-rewind')
        let progress = document.querySelector('.pcast-progress')
        let speed = document.querySelector('.pcast-speed')
        let mute = document.querySelector('.pcast-mute')
        let currentTime = document.querySelector('.pcast-current-time')
        let duration = document.querySelector('.pcast-duration')
        
        pause.style.display = 'none'
        
        audio.addEventListener('loadedmetadata', function() {
            progress.setAttribute('max', parseInt(this.duration))
            duration.textContent  = formatTime(this.duration)
        })
        
        audio.addEventListener('timeupdate', function() {
            progress.setAttribute('value', this.currentTime)
            currentTime.textContent  = formatTime(this.currentTime)
        })
        
        play.addEventListener('click', function() {
            this.style.display = 'none'
            pause.style.display = 'inline-block'
            pause.focus()
            audio.play()
        }, false)

        pause.addEventListener('click', function() {
            this.style.display = 'none'
            play.style.display = 'inline-block'
            play.focus()
            audio.pause()
        })

        rewind.addEventListener('click', () => {
            audio.currentTime -= 30
        })
        
        progress.addEventListener('click', e => {
            audio.currentTime = Math.floor(audio.duration) * (e.offsetX / e.target.offsetWidth)
        })

        speed.addEventListener('click', function() {
            currentSpeedIndex = currentSpeedIndex + 1 < speeds.length ? currentSpeedIndex + 1 : 0
            audio.playbackRate = speeds[currentSpeedIndex]
            this.textContent  = speeds[currentSpeedIndex] + 'x'
            return true
        })

        mute.addEventListener('click', function() {
            audio.muted = !audio.muted
            this.querySelector('.fa').classList.toggle('fa-volume-off')
            this.querySelector('.fa').classList.toggle('fa-volume-up')
        })
    }
})

/**
 * Función que establece la altura mínima que debe tener el primer elemento hijo del body, 
 * para asegurarse de que el footer se mantiene siempre en el borde inferior de la pantalla 
 * del viewport.
 * 
 * NOTA: se desecha usar este método debido a la transición molesta que se produce al cargar 
 * o refrescar la página y en su lugar establecer la altura mínima directamente desde la hoja 
 * de estilos (aunque sea un proceso estático el resultado es visualmente más apetecible).
 */
let setContainerMinHeight = container => {
    container.style.minHeight = `calc(100vh - ${$('nav').get(0).offsetHeight}px - ${$('.footer').get(0).offsetHeight}px)`
}

/**
 * Función que trunca una cadena ocultando su contenido intermedio con puntos suspensivos 
 * dejando solamente visibles un número de caracteres iniciales especificado por parámetro
 * y los siete últimos caracteres (útil para acortar la longitud de sin esconder su extensión).
 */
let truncate = (string, n) => (string.length > n) ? (string.substr(0, n - 8) + ' ... ' + string.slice(-7)) : string

/**
 * Función que recibe un número de segundos como parámetro y devuelve una cadena con formato 
 * de hora (HH:mm:ss).
 */
let formatTime = s => {
    let totalSeconds = parseInt(s, 10)
    let hh = parseInt(totalSeconds / 60 / 60)
    let mm = parseInt((totalSeconds / 60) % 60)
    let ss = totalSeconds % 60

    hh = (hh < 10 ? '0' : '') + hh
    mm = (mm < 10 ? '0' : '') + mm
    ss = (ss < 10 ? '0' : '') + ss

    return `${hh}:${mm}.${ss}`
}

/**
 * Función que comprueba si fichero cargado en campo tipo file es imagen y no excede el límite permitido 
 * y, en caso de cumplir ambas condiciones, muestra el título en el campo y extrae los metadadatos desde 
 * su ubicación en el dispositivo local para poder mostrarla en la aplicación en un elemento img.
 */
function readImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0]

        if (!file.type || file.type.indexOf('image') === -1) {
            input.value = ''
            alert('¡Fichero no es una imagen!')
            return
        } else if (file.size / (1024 * 1024) > 256) {
            input.value = ''
            alert('¡Fichero excede límite permitido (256MB)!')
            return
        }
        
        const reader = new FileReader()
        reader.onload = function(e) {
            $('#btnResetImg').removeClass('invisible')
            $('#imgInput').next().text( truncate(file.name, 25) )
            $('#imgDefault').addClass('d-none')
            $('#imgCustom').removeClass('d-none')
            $('#imgCustom').attr('src', e.target.result)
        }
        reader.readAsDataURL(file)
    }
}

/**
 * Función que comprueba si fichero cargado en campo tipo file es aucio y no excede el límite permitido 
 * y, en caso de cumplir ambas condiciones, muestra el título en el campo.
 */
function readAudio(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0]

        if (!file.type || file.type.indexOf('audio') === -1) {
            input.value = ''
            alert('¡Fichero no es una pista de audio!')
            return
        } else if (file.size / (1024 * 1024) > 256) {
            input.value = ''
            alert('¡Fichero excede límite permitido (256MB)!')
            return
        }

        $('#audioInput').next().text( truncate(file.name, 100) )
    }
}

/**
 * Función que vacía el campo tipo input pasado por parámetro, sustituye la imagen actual por la imagen por 
 * defecto y oculta el botón de cierre.
 */
function resetFileInput(button, input) {
    button.addClass('invisible')
    input.value = ''
    $('#imgInput').next().text($('#imgUpdate').val() ?? 'Subir imagen')
    $('#imgDefault').removeClass('d-none')
    $('#imgCustom').addClass('d-none')
    $('#imgCustom').attr('src', '')
}

/**
 * Función que carga dinámicamente mediante AJAX la lista de valores en el elemento select correspondiente
 * cada vez que se elige un valor del elemento select superior, reseteando previamente los valores que
 * pudiesen haber sido cargados con anterioridad.
 */
function loadSelect(dropdownIdArray, parameterName, parameterValue, value, text) {
    $.each(dropdownIdArray, function(i, val) {
        $(`#${val}`).children().remove().end()
            .append('<option value="" disabled selected></option>')
    })

    let codCentro = $('#codCentro').val()
    let url = codCentro
        ? `./get-school.php?${parameterName}=${parameterValue}&codigo-centro=${codCentro}`
        : `./get-school.php?${parameterName}=${parameterValue}`

    $.getJSON(url)
        .done(function(result) {
            $.each(result, function(i, item) {
                $(`#${dropdownIdArray[0]}`).append($("<option />").val(item[value]).text(item[text]))
            })
        })
        .fail(function(error) { 
            console.error(error) 
        })
}