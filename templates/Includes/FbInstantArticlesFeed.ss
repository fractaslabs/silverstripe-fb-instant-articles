<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
		<title>$SiteConfig.Title</title>
		<description>$SiteConfig.Tagline</description>
		<link>$AbsoluteLink</link>
		<language>$ContentLocale</language>
		<lastBuildDate>$LastEditedIso</lastBuildDate>
		<% if Items %>
		<% loop Items %>
		<item>
			<title>$Title</title>
			<link>$AbsoluteLink</link>
			<guid isPermaLink="false">$ID</guid>
			<pubDate>$PublishedDateIso</pubDate>
			<modDate>$LastEditedIso</modDate>
			<description>$Description</description>
			<% if Authors %><% loop Authors %><author>$FullName</author><% end_loop %><% end_if %>
			<content:encoded><![CDATA[
				$Content
			]]></content:encoded>
		</item>
		<% end_loop %>
		<% end_if %>
	</channel>
</rss>
