<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"

	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:lde="http://schema.legsb.gov.uk/lde/"
	xmlns:dict="http://schema.legsb.gov.uk/lde/dictionary/"

  	exclude-result-prefixes="xsl lde dict"
  	>

	<xsl:output method="xml" omit-xml-declaration="yes"/>

  	
<xsl:template match="/">
	<table class="list">
		<thead>
			<tr>
				<th>Concept</th><th>Dictionary</th><th>Class</th>
			</tr>
		</thead>
		<tbody>

			<xsl:for-each select="/lde:Classes/dict:Dictionary/dict:Class">
				<xsl:sort select="@concept"/>
				<xsl:sort select="dict:Heading"/>

				<xsl:variable name="DictId">
					<xsl:value-of select="../@id"/>
				 </xsl:variable>
				  
				<tr>
					<td>
						<xsl:value-of select="@concept"/>
					</td>				
					<td>
						<xsl:value-of select="$DictId"/>							
					</td>				

					<td>
						<a>
							<xsl:attribute name="href">
					 			<xsl:text>browse.php?dictid=</xsl:text>
					 			<xsl:value-of select="$DictId"/>
					 			<xsl:text>&amp;classid=</xsl:text>
					 			<xsl:value-of select="@id"/>							 			
							</xsl:attribute>
							<xsl:value-of select="dict:Heading"/>
						</a>
					</td>
							
			  	</tr>
			</xsl:for-each>
		</tbody>
	  
	</table>

</xsl:template>	

</xsl:stylesheet>