<script type="text/javascript">
// <![CDATA[
	// preload some images
	Image1= new Image(16,16);
	Image1.src = "{UA_GREY_STAR_SRC}";
	Image2= new Image(16,16);
	Image2.src = "{UA_GREEN_STAR_SRC}";
	Image3= new Image(16,16);
	Image3.src = "{UA_RED_STAR_SRC}";
	Image4= new Image(16,16);
	Image4.src = "{UA_ORANGE_STAR_SRC}";

	// Some other variables
	var max_rating = "{UA_MAX_RATING}";
	var min_rating = "{UA_MIN_RATING}";

	function toggleDiv(divName)
	{
	    thisDiv = document.getElementById(divName);
	    if (thisDiv)
		{
	        if (thisDiv.style.display == "none")
			{
	            thisDiv.style.display = "block";
	        }
	        else
			{
	            thisDiv.style.display = "none";
	        }
	    }
	}

	/*
	* for ratings
	*/
	function ratingHover(id, name)
	{

		for (var i = min_rating; i <= max_rating; i++)
		{
			star=document.getElementById(name + i);

			if (i <= id)
			{
				star.src = "{UA_RED_STAR_SRC}";
			}
			else
			{
				star.src = "{UA_GREY_STAR_SRC}";
			}
		}
	}

	function ratingUnHover(id, name)
	{

		for (var i = min_rating; i <= max_rating; i++)
		{
			star=document.getElementById(name + i);

			if (i <= id)
			{
				star.src = "{UA_ORANGE_STAR_SRC}";
			}
			else
			{
				star.src = "{UA_GREY_STAR_SRC}";
			}
		}
	}

	function ratingDown(id, name)
	{

		for (var i = min_rating; i <= max_rating; i++)
		{
			star=document.getElementById(name + i);

			if (i <= id)
			{
				star.src = "{UA_GREEN_STAR_SRC}";
			}
			else
			{
				star.src = "{UA_GREY_STAR_SRC}";
			}
		}
	}
// ]]>
</script>