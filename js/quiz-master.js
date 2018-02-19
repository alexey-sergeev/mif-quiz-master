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
    

    
    
    // Кнопка "Далее"
    
    jq( 'body' ).on( 'submit', '.quiz form', function() {
        
        var data = new FormData( this );
        
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

    } )


    // Кнопка "Добавить" (пользователей)

    jq( 'body' ).on( 'click', '.add-button button', function() {
        
        var add_form = jq( this ).closest( '.add-form' );
        var add_textarea = jq( '.add-textarea', add_form );
        var add_button = jq( '.add-button', add_form );

        add_button.slideUp( function() { add_textarea.css( 'display', 'flex' ).hide().slideDown(); } )

        return false;

    } );


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


        jq.post( ajaxurl, {
            action: 'members',
            quiz_id: quiz_id,
            new_members_data: data,
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

    

});