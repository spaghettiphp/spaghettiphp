<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="shortcut icon" href="http://spaghettiphp.org/images/favicon.png" type="image/png" />
    <title>Spaghetti* Framework &mdash; Seja bem vindo!</title>
    
    <?php echo $html->stylesheet("spaghetti.css"); ?>
    
    <script type="text/javascript">
        window.onload = function(){
            var list = document.getElementsByTagName("ol")[0].getElementsByTagName("li");
            for(i=0; i<list.length; i++){
                var title = list[i].getElementsByTagName("strong")[0]
                title.onclick = function() {
                    var text = this.parentNode.getElementsByTagName("p")[0]
                    text.style.display = (text.style.display=="block")? "none" : "block"
                }
            }
        }
    </script>
</head>
<body>

<?php echo $this->contentForLayout ?> 

</body>
</html>
