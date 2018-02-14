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
    
    
});