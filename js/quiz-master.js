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
                    // jq( '#mif-qm-ajax-container .quiz-content' ).animate( { 'opacity': 0 }, function() {
                        
                    //     jq( '#mif-qm-ajax-container .quiz-content' ).animate( { 'opacity': 1 } );
            
                    // } )
                

                    // jq( '#mif-qm-ajax-container' ).animate( { 'opacity': 0 }, function() {

                    //     jq( '#mif-qm-ajax-container' ).html( response );
                    //     jq( '#mif-qm-ajax-container' ).animate( { 'opacity': 1 } );
            
                    // } )
            

                    console.log(response);
                    
                } else {
                    
                    console.log( 'error 1' );
                    
                }
                
            },
            error: function( response ) {
                
                console.log( 'error 2' );

            },
        } );



        // jq.ajax({
        //     type: 'POST',
        //     url: 'res.php',
        //     data: msg,
        //     success: function(data) {
        //       $('#results').html(data);
        //     },
        //     error:  function(xhr, str){
        //   alert('Возникла ошибка: ' + xhr.responseCode);
        //     }
        //   });





        // jq.post( ajaxurl, {
        //     action: 'mif-qm-quiz-submit',
        //     data: data
        // },
        // function( response ) {

        //     console.log(response);

        // });




        // console.log(data);
       
        return false;


        // var recipients = jq( '.messages-wrap .recipients' );
        // var message = jq( '#message', this ).val();
        // var nonce = jq( '#nonce', this ).val();
        // var email = ( jq( '#email', this ).prop( 'checked' ) ) ? 1 : 0;
        // var subject = jq( '#subject', this ).val();
        // var recipient_ids = [];

        // if ( recipients.html().trim() === '' ) {

        //     jq( '.messages-wrap .recipients' ).addClass( 'warning' );

        // } else if ( message.trim() === '' ) {

        //     jq( '.messages-wrap .textarea' ).addClass( 'warning' );

        // } else {

        //     jq( '.messages-wrap .recipients .member-item' ).each( function( i, elem ) { recipient_ids.push( jq( elem ).attr( 'data-uid' ) ); } );
            
        //     jq.post( ajaxurl, {
        //         action: 'mif-bpc-dialogues-compose-send',
        //         _wpnonce: nonce,
        //         email: email,
        //         message: message,
        //         subject: subject,
        //         recipient_ids: recipient_ids,
        //     },
        //     function( response ) {

        //         modify_page( response ); 
        //         jq( '.thread-wrap .member-item' ).removeClass( 'checked' );
        //         // console.log(response);

        //     });

        //     recently_flag = false;
        // }


    } );
    


    
});