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
				<th>Id</th><th>Shape</th><th>Subject Identifier</th><th>Subject Name</th>
			</tr>
		</thead>
		<tbody>
  
			<xsl:for-each select=".//lde:Documents/lde:Document">
			  
				<tr>
				
						<td>
							<a>
								<xsl:attribute name="href">
						 			<xsl:text>document.php?docid=</xsl:text>
						 			<xsl:value-of select="@id"/>				
								</xsl:attribute>
								<xsl:value-of select="@id"/>												
							</a>
						</td>
								
										
						<td>
							<xsl:value-of select="lde:Shape/lde:Name"/>
						</td>

						<td>
							<xsl:value-of select="lde:Subjects/lde:Subject[1]/@identifier"/>
						</td>

						
						<td>
							<xsl:value-of select="lde:Subjects/lde:Subject[1]/@name"/>
						</td>
						
				  	</tr>
			</xsl:for-each>
		</tbody>
	  
	</table>

</xsl:template>	

</xsl:stylesheet>