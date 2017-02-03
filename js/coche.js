function cocher()
{
	var nom = "coche\[\]";
	if(document.getElementById("cochertout").checked == true)
	{
		$("input:checkbox[name='" + nom + "']").prop('checked', true);
	}
	else
	{
		$("input:checkbox[name='" + nom + "']").prop('checked', false);
	}
}

function cocher2()
{
	var nom = "coche2\[\]";
	if(document.getElementById("cochertout2").checked == true)
	{
		$("input:checkbox[name='" + nom + "']").prop('checked', true);
	}
	else
	{
		$("input:checkbox[name='" + nom + "']").prop('checked', false);
	}
}