<script type="text/javascript">
	function toggleDiv(divName, toggle)
	{
	    thisDiv = document.getElementById(divName);
	    if (thisDiv)
		{
	        if (thisDiv.style.display == "none" || toggle == "show")
			{
	            thisDiv.style.display = "block";
	        }
	        else
			{
	            thisDiv.style.display = "none";
	        }
	    }
	}

	function toggle_month(month)
	{
		thisMonth = document.getElementById('month_' + month);
		thisImg = document.getElementById('month_image_' + month);

		if (thisMonth && thisImg)
		{
			if (thisMonth.style.display == "none")
			{
				thisMonth.style.display = "block";
				thisImg.src = "{T_THEME_PATH}/images/minus.gif";
			}
			else
			{
				thisMonth.style.display = "none";
				thisImg.src = "{T_THEME_PATH}/images/plus.gif";
			}
		}
	}
</script>