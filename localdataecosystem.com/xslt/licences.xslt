<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"

	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:lde="http://schema.legsb.gov.uk/lde/"
	xmlns:rights="http://schema.legsb.gov.uk/lde/rights/"
	xmlns:xhtml ="http://www.w3.org/1999/xhtml"

  	exclude-result-prefixes="xsl lde rights xhtml"
  	>

	<xsl:output method="xml" omit-xml-declaration="yes"/>

  	
<xsl:template match="/">
	<table class="list">
		<thead>
			<tr>
				<th>Name</th><th>Description</th>
			</tr>
		</thead>
		<tbody>
  
			<xsl:for-each select=".//rights:Licence">
			  
				<tr>
				
						<td>
							<a>
								<xsl:attribute name="href">
						 			<xsl:text>licence.php?licenceid=</xsl:text>
						 			<xsl:value-of select="@id"/>				
								</xsl:attribute>
								<xsl:value-of select="rights:Name"/>
							</a>
						</td>
								
										
						<td>
							<xsl:value-of select="rights:Description"/>
						</td>

								
				  	</tr>
			</xsl:for-each>
		</tbody>
	  
	</table>

</xsl:template>	

</xsl:stylesheet>