<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"

	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:lde="http://schema.legsb.gov.uk/lde/"
	xmlns:xhtml ="http://www.w3.org/1999/xhtml"

  	exclude-result-prefixes="xsl lde xhtml"
  	>

	<xsl:output method="xml" omit-xml-declaration="yes"/>

  	
<xsl:template match="/">
	<table class="list">
		<thead>
			<tr>
				<th>Name</th><th>Context</th><th>Licence Type</th><th>Organisation</th><th>Shape</th><th>Purpose</th>
			</tr>
		</thead>
		<tbody>
  
			<xsl:for-each select=".//lde:Set">
			  
				<tr>
				
						<td>
							<a>
								<xsl:attribute name="href">
						 			<xsl:text>set.php?setid=</xsl:text>
						 			<xsl:value-of select="@id"/>				
								</xsl:attribute>
								<xsl:value-of select="lde:Name"/>
							</a>
						</td>
								
										
						<td>
							<xsl:value-of select="@context"/>
						</td>

						<td>
							<xsl:value-of select="@licenceType"/>
						</td>

						
						<td>
							<xsl:value-of select="lde:Organisation/lde:Name"/>
						</td>
						
						<td>
							<xsl:for-each select="lde:Shapes/lde:Shape">
								<xsl:value-of select="lde:Name"/>
								<br/>	
							</xsl:for-each>
						</td>
				
						<td>
							<xsl:for-each select="lde:Purposes/lde:Purpose">
								<xsl:value-of select="lde:Name"/>
								<br/>	
							</xsl:for-each>
						</td>
								
				  	</tr>
			</xsl:for-each>
		</tbody>
	  
	</table>

</xsl:template>	

</xsl:stylesheet>