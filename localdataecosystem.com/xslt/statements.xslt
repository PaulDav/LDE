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
				<th>Doc</th><th>Id</th><th>Type</th><th>About</th><th>Subject</th><th>Link</th><th>Value</th><th>Eff From</th><th>Eff To</th>
			</tr>
		</thead>
		<tbody>
  
			<xsl:for-each select=".//lde:Statements/lde:Statement">
			  
				<tr>
				
						<td>
							<a>
								<xsl:attribute name="href">
						 			<xsl:text>document.php?docid=</xsl:text>
						 			<xsl:value-of select="@docid"/>				
								</xsl:attribute>
								<xsl:value-of select="@docid"/>
							</a>
						</td>

						<td>
							<a>
								<xsl:attribute name="href">
						 			<xsl:text>statement.php?statid=</xsl:text>
						 			<xsl:value-of select="@id"/>				
								</xsl:attribute>
								<xsl:value-of select="@id"/>												
							</a>
						</td>
								
										
						<td>
							<xsl:value-of select="@type"/>
						</td>

						<td>
							<xsl:value-of select="@aboutid"/>
						</td>

						<td>
							<xsl:value-of select="@subjectid"/>
						</td>

						<td>
							<xsl:value-of select="@linklabel"/>
						</td>

						<td>
							<xsl:value-of select="@value"/>
							<xsl:value-of select="@objectid"/>							
						</td>
						
						<td>
							<xsl:value-of select="@effectivefrom"/>
						</td>

						<td>
							<xsl:value-of select="@effectiveto"/>
						</td>
						
				  	</tr>
			</xsl:for-each>
		</tbody>
	  
	</table>

</xsl:template>	

</xsl:stylesheet>