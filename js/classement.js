$(document).ready(function(){
    $(".classementBouton").click(function(){
        $(this).parent().find('table.classement').toggle();
		
		if($(this).parent().find('table.classement').css('display') == 'table')
		{
			$(this).text("↑ Masquer le classement ↑");
		}
		else
		{
			$(this).text("↓ Afficher le classement ↓");
		}
    });
});