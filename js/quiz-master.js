// 
// JS-методы плагина Quiz Master
// 


jQuery( document ).ready( function( jq ) {
    
    // Кнопка "Актуальные"

    jq( '.result_list' ).on( 'click', 'a.show-current', function() {

        // jq( '.result_list .row.all' ).css( 'display', 'none' );
        jq( '.result_list .row.all' ).slideUp();
        jq( '.result_list .show-all' ).show();
        jq( '.result_list .show-current' ).hide();
        return false;
        
    } );
    
    
    // Кнопка "Все"
    
    jq( '.result_list' ).on( 'click', 'a.show-all', function() {
        
        // jq( '.result_list .row.all' ).css( 'display', 'flex' );
        jq( '.result_list .row.all' ).css( 'display', 'flex' ).hide().slideDown();
        jq( '.result_list .show-all' ).hide();
        jq( '.result_list .show-current' ).show();
        return false;

    } );
    

    
    
    // Кнопка различных действий в тесте - сам тест, настройка пользователей и др.

    jq( 'body' ).on( 'click', '.quiz form button:not(.noajax)', function() {
        
        var form = jq( this ).closest( 'form' );
        var data = new FormData( form.get(0) );
        
        var btn_value = jq( this ).prop( 'name' );
        if ( btn_value ) data.append( 'do', btn_value ); 
        
        show_loading( this );
                    
        jq.ajax( {
            url: ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: data,
            success: function( response ) {

                if ( response ) {

                    jq( '#mif-qm-ajax-container' ).html( response );
                    // console.log(response);
                    
                } else {
                    
                    console.log( 'error 1' );
                    
                }
                
            },
            error: function( response ) {
                
                console.log( 'error 2' );

            },
        } );
       
        return false;

    } );
    

  
    
    // Кнопки добавления или удаления конкретных пользователей
    
    jq( 'body' ).on( 'click', '.member-manage-btn', function() {
    
        var action_do = jq( this ).attr( 'data-do' );
        var member = jq( this ).attr( 'data-member' );
        var form = jq( this ).closest( 'form' );

        var nonce = jq( 'input[name=_wpnonce]', form ).val();
        var quiz_id = jq( 'input[name=quiz_id]', form ).val();
        var premise = jq( 'input[name=premise]', form ).val();

        show_loading( this );        
        // console.log(action_do);
        // console.log(member);
        // console.log(quiz_id);
        // console.log(form.html());

        jq.post( ajaxurl, {
            action: 'members',
            do: action_do,
            members: member,
            quiz_id: quiz_id,
            premise: premise,
            _wpnonce: nonce,
        },
        function( response ) { 

            if ( response ) {

                jq( '#mif-qm-ajax-container' ).html( response );
                // console.log(response);

            }

        });
        
        return false;
    } );
  


    
    // Кнопки навигации по тесту
    
    jq( 'body' ).on( 'click', '.quiz-navigation a', function() {

        var num = jq( this ).attr( 'data-num' );
        var nonce = jq( this ).attr( 'data-nonce' );
        var quiz_id = jq( this ).attr( 'data-quiz_id' );
        var action = 'run';

        jq.post( ajaxurl, {
            action: action,
            num: num,
            quiz_id: quiz_id,
            _wpnonce: nonce,
        },
        function( response ) { 

            if ( response ) {

                jq( '#mif-qm-ajax-container' ).html( response );
                // console.log(response);

            }

        });

        return false;

    } );

        
    
    // Кнопка "Добавить" (пользователей)

    jq( 'body' ).on( 'click', '.quiz form button.textarea-show', function() {

        var form = jq( this ).closest( 'form' );
        var div = jq( '.add-textarea', form );

        // div.show();
        div.css( 'display', 'flex' ).hide().slideDown();

        // var add_textarea = jq( '.add-textarea', add_form );
        // var add_button = jq( '.add-button', add_form );

        // add_button.slideUp( function() { add_textarea.css( 'display', 'flex' ).hide().slideDown(); } )
        
        return false;

    } );

    // // Кнопка "Добавить" (пользователей)

    // jq( 'body' ).on( 'click', '.add-button button', function() {
        
    //     var add_form = jq( this ).closest( '.add-form' );
    //     var add_textarea = jq( '.add-textarea', add_form );
    //     var add_button = jq( '.add-button', add_form );

    //     add_button.slideUp( function() { add_textarea.css( 'display', 'flex' ).hide().slideDown(); } )

    //     return false;

    // } );


    // Кнопка "Отмена" (добавления пользователей)

    jq( 'body' ).on( 'click', '.add-form a.cancel', function() {
        
        var add_form = jq( this ).closest( '.add-form' );
        var add_textarea = jq( '.add-textarea', add_form );
        var add_button = jq( '.add-button', add_form );

        add_textarea.slideUp( function() { add_button.css( 'display', 'flex' ).hide().slideDown(); } )

        return false;

    } );


    // Кнопка "Сохранить" (пользователей)

    jq( 'body' ).on( 'click', '.save-button button', function() {
        
        var add_form = jq( this ).closest( '.add-form' );
        var data = jq( '.add-textarea textarea', add_form ).val();
        var nonce = jq( 'input[name=_wpnonce]', add_form ).val();
        var quiz_id = jq( 'input[name=quiz_id]', add_form ).val();

        // add_textarea.slideUp( function() { add_button.css( 'display', 'flex' ).hide().slideDown(); } )
        show_loading( this );

        jq.post( ajaxurl, {
            action: 'members',
            quiz_id: quiz_id,
            members: data,
            _wpnonce: nonce,
        },
        function( response ) { 

            if ( response ) {

                jq( '#mif-qm-ajax-container' ).html( response );
                // console.log(response);

            }

        });

        return false;

    } );

    


    // Выбор режима доступа (радиокнопки)

    jq( 'body' ).on( 'click', '.access-mode input[type=radio]', function() {


        var form = jq( this ).closest( '.access-mode' );
        var r_btn = jq( 'input:radio[name=access_mode]:checked', form );
        var access_mode = r_btn.val();
        var nonce = jq( 'input[name=_wpnonce]', form ).val();
        var quiz_id = jq( 'input[name=quiz_id]', form ).val();
        
        // Анимация при переключении

        var div1 = jq( '.access-mode .bg-primary' );
        var div2 = r_btn.closest( 'div' );
        var loading = jq( '.loading', div2 );
        div1.removeClass( 'bg-primary text-light' );
        div1.addClass( 'bg-light' );
        div2.removeClass( 'bg-light' );
        div2.addClass( 'bg-primary text-light' );
        // loading.fadeIn( 'fast' );
        loading.show();
        
        // Отправить данные

        jq.post( ajaxurl, {
            action: 'members',
            quiz_id: quiz_id,
            access_mode: access_mode,
            _wpnonce: nonce,
        },
        function( response ) { 

            if ( response ) {

                // loading.fadeOut( 'fast', function() { jq( '#mif-qm-ajax-container' ).html( response ); } );
                jq( '#mif-qm-ajax-container' ).html( response );
                // console.log(response);

            }

        });

        // return false;

    } );


    
    
    // Кнопки ввода инвайта
    
    jq( 'body' ).on( 'submit', '.invite form', function() {


        var data = new FormData( this );

        jq.ajax( {
            url: ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: data,
            success: function( response ) {

                if ( response ) {

                    // console.log( response );
                    window.location.href = response;
                    
                } else {
                    
                    jq( '.invite form input[name=invite_code]' ).val( '' );
                    // console.log( 'error 7' );
                    
                }
                
            },
            error: function( response ) {
                
                console.log( 'error 8' );

            },

        } );


        return false;
    } );

    
    
    // Кнопки каталога
    
    jq( 'body' ).on( 'submit', '.catalog form', function() {
        
        var data = new FormData( this );
        // var div = jq( this ).closest( 'div.next-page' );
        var div = jq( 'div.next-page', this );

        var button = jq( 'button', div );
        var loading = jq( '.loading', div );

        button.hide();
        loading.fadeIn();

        // Запрос на обновление каталога

        jq.ajax( {
            url: ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: data,
            success: function( response ) {

                if ( response ) {

                    div.replaceWith( response )
                    // console.log( response );
                    
                } else {
                    
                    console.log( 'error 3' );
                    
                }
                
            },
            error: function( response ) {
                
                console.log( 'error 4' );

            },

        } );

        
        // Запрос на обновление статистики

        data.append( 'mode', 'stat' );

        jq.ajax( {
            url: ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: data,
            success: function( response ) {

                if ( response ) {

                    jq( '.catalog .stat p' ).html( response );
                    // div.replaceWith( response )
                    // console.log( response );
                    
                } else {
                    
                    console.log( 'error 5' );
                    
                }
                
            },
            error: function( response ) {
                
                console.log( 'error 6' );

            },

        } );


        return false;

    } );    



    // Выбор категории в каталоге

    jq( 'body' ).on( 'click', '.catalog a.category', function() {
        
        var div = jq( this ).closest( 'div' );
        var list_item = jq( '.list-item', div );
        var name = jq( this ).attr( 'data-name' );
        // var id = jq( this ).attr( 'data-id' );
        var input = jq( 'input[type=hidden]', div );
        
        // Поменять внешний вид категории

        list_item.toggleClass( 'bg-primary' );
        list_item.toggleClass( 'text-light' );
        jq( this ).toggleClass( 'text-primary' );

        // Уточнить внутренние параметры

        var value = ( list_item.hasClass( 'bg-primary' ) ) ? name : '';
        input.val( value );

        clear_box();

        // Отправить форме, что она submit

        jq( '.catalog form' ).trigger( 'submit' );

        return false;

    } );
    
    
    
    // Ввод в строку поиска
    
    var search_timeout;

    jq( 'body' ).on( 'input', '.catalog input[name=quiz_search]', function() {

        clearTimeout( search_timeout );

        search_timeout = setTimeout( function() {

            clear_box();
            jq( '.catalog form' ).trigger( 'submit' );

        }, 800 );
        
    } );
    
    
    
    // "Выбрать всех" в списке пользователей
    
    jq( 'body' ).on( 'click', 'input[name=select_all]', function() {

        var form = jq( this ).closest( 'form' );
        var input = jq( 'input.members', form );

        input.prop( 'checked', jq( this ).prop( 'checked' ) );

        // console.log();
        
    } );


   
    
    //
    // Очистить страницу каталога для новой выдачи
    //
    
    function show_loading( elem )
    {
        var div = jq( elem ).closest( 'div' );
        var loading = jq( '.loading', div );
        loading.fadeIn();
    }



    //
    // Очистить страницу каталога для новой выдачи
    //

    function clear_box()
    {

        // Просить первую страницу
        
        jq( 'input[name=page]' ).val( 1 );

        // Удалить старые записи и др.

        jq( '.catalog .card' ).remove();
        // jq( '.catalog .stat' ).remove();

    }



});