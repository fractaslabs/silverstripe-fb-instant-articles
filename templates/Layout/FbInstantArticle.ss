<!doctype html>
<html lang="$ContentLocale" prefix="op: http://media.facebook.com/op#">
	<head>
		<meta charset="utf-8">
		<link rel="canonical" href="$AbsoluteLink">
		<link rel="stylesheet" title="default" href="#">
		<title>$Title</title>
		<meta property="op:markup_version" content="v1.0">
		<meta property="fb:use_automatic_ad_placement" content="false">
		<meta property="op:tags" content="$TagsList">
		<meta property="fb:article_style" content="default">
	</head>
	<body>
		<article>
			<header>
				<% if Image %>
				<figure>
					<img src="$Image.CroppedFocusedImage(1024,1024).AbsoluteURL" />
					<% if $Image.Description || $Image.Origin %>
						<figcaption>
							<h1>$Image.Description</h1>
							<% if $Image.Origin %><cite>$Image.Origin</cite><% end_if %>
						</figcaption>
					<% end_if %>
				</figure>
				<% end_if %>
				<% if Video %>
				<figure>
					<video>
						<source src="$Video" type="video/mp4" />
					</video>
				</figure>
				<% end_if %>
				<% if Images %>
				<figure class="op-slideshow">
					<% loop Images %>
					<figure>
						<img src="$CroppedFocusedImage(1024,1024).AbsoluteURL" />
						<% if $Description %><figcaption>$Description</figcaption><% end_if %>
					</figure>
					<% end_loop %>
				</figure>
				<% end_if %>

				<h1>$Title</h1>
				<% if SubTitle %>
				<h3 class="op-kicker">$SubTitle</h3>
				<% end_if %>
				<% if LeadText %>
				<h2>$LeadText</h2>
				<% end_if %>

				<time class="op-published" dateTime="$PublishedDateIso">$PublishedDateLong</time>
				<time class="op-modified" dateTime="$LastEditedIso">$LastEditedLong</time>

				<% if Authors %>
					<% loop Authors %>
						<address>$FirstName $Surname</address>
					<% end_loop %>
				<% end_if %>

				<%--
				<!-- Ad to be automatically placed throughout the article -->
				<figure class="op-ad">
					<iframe src="https://www.adserver.com/ss;adtype=banner320x50" height="50" width="320"></iframe>
				</figure>
				--%>

			</header>
			$Content
			<figure class="op-tracker">
				<iframe>
					<script>
						(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
							(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
							m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
							})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
							ga('create', '$GAID', 'auto');
						<% if IsDisplayFeatured %>ga('require', 'displayfeatures');<% end_if %>
							ga('send', 'pageview');
						<% if MultiTrackersList %>
						<% loop MultiTrackersList %>
							ga('create', '$ID', 'auto', {'name': '$Title'});
							ga('{$Title}.send', 'pageview');
							ga(function() { var $Title = ga.getByName('$Title'); });
						<% end_loop %>
							ga(function() { var allTrackers = ga.getAll(); });﻿
						<% end_if %>
					</script>
				</iframe>
			</figure>
			<footer>
				<small>Copyright &copy;{$Now.Year} {$SiteConfig.Title} Sva prava pridržana.</small>
			</footer>
		</article>
	</body>
</html>
