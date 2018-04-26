<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
	    <title>跳转提示</title>
	    <link rel="stylesheet" href="/public/assets/css/ace.min.css" id="main-ace-style" />
	    <link rel="stylesheet" href="/public/assets/css/font-awesome.min.css" />
	    <style>
	    	.error_container{
	    		width:100%;
	    		overflow:hidden;
	    	}
	    	.mywell{
				text-align:center;
	    		padding-top:50px;
	    	}
	    	.mywell a{
	    		text-decoration:none;
	    		color:#6fb3e0;
	    	}
	    </style>
	</head>
	<body>
		<div class="error_container">
			<div class="mywell">
				<{if $code==1}>
				<h1 class="grey lighter smaller">
					<span class="green bigger-125">
						<i class="fa fa-check"></i>
					</span>
					<span class="grey bigger-125">
						<{$msg|strip_tags}>
					</span>
				</h1>
				<{else}>
				<h1 class="grey lighter smaller">
					<span class="orange bigger-125">
						<i class="fa fa-exclamation-triangle"></i>
					</span>
					<span class="grey bigger-125">
						<{$msg|strip_tags}>
					</span>
				</h1>
				<{/if}>
				<div class="space"></div>
				<div class="space"></div>
				<hr />
				<div class="center">
					<span class="bigger-120">
			            页面自动 <a id="href" href="<{$url}>">跳转</a> 等待时间： <b id="wait"><{$wait}></b>
			        </span>
				</div>
			</div>
		</div>
	</body>
</html>
<script type="text/javascript">

	(function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();


</script>

