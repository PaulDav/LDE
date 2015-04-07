<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"

	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:lde="http://schema.legsb.gov.uk/lde/"
	xmlns:dict="http://schema.legsb.gov.uk/lde/dictionary/"

  	exclude-result-prefixes="xsl lde dict"
  	>

  	<xsl:output method="xml" omit-xml-declaration="yes"/>

	<xsl:param name='mode'>
		<xsl:text>html</xsl:text>
	</xsl:param>

	<xsl:param name='color'>
		<xsl:text>white</xsl:text>
	</xsl:param>


	<xsl:param name='FieldName'>
		<xsl:text>subjectid</xsl:text>
	</xsl:param>

	<xsl:param name='returnUrl'>
		<xsl:text>subject.php</xsl:text>
	</xsl:param>
	<xsl:param name='ShapeId'/>


	<xsl:variable name="Class" select="/lde:Subjects/lde:Class"/>

	<xsl:variable name="NumberOfHeadingRows">
		<xsl:choose>
			<xsl:when test="$Class//lde:ComplexProperties">
				<xsl:for-each select="$Class//lde:ComplexProperties">
					<xsl:sort select="count(ancestor::*)" data-type="number"/>
					<xsl:if test="position()=last()">
<!--						<xsl:value-of select="position()+1"/> -->
						<xsl:value-of select="position()"/>
					</xsl:if>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>1</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:variable>
	
	<xsl:variable name="NumberOfCols">
		<xsl:text>10</xsl:text>
	</xsl:variable>

  	
<xsl:template match="/">

	<xsl:choose>
		<xsl:when test="$mode='dot'">
			<xsl:call-template name="StyleForDot"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="StyleForHtml"/>		
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="StyleForHtml">

	<table class="list">
		<thead>
			<xsl:call-template name='HeadingRow'/>			
		</thead>
		
		<tbody>
			<xsl:for-each select="/lde:Subjects/lde:Subject">
				<xsl:variable name="Subject" select="."/>
				
				<tr>
				
					<td>

						<a>
							<xsl:attribute name='href'>
								<xsl:text>set.php?setid=</xsl:text>
								<xsl:value-of select="@setid"/>								
							</xsl:attribute>
							<xsl:value-of select="@setname"/>
						</a>
					</td>
					
					<td>
					
						<a>
							<xsl:call-template name="href"/>
							<xsl:value-of select="@id"/>
						</a>
					</td>
				
					<xsl:call-template name='DataCells'>
						<xsl:with-param name="Subject" select="$Subject"/>
						<xsl:with-param name="Attributes" select="$Subject/lde:Attributes"/>
					</xsl:call-template>
					
				</tr>
			</xsl:for-each>
		
		
		</tbody>
	  
	</table>

</xsl:template>	


<xsl:template name="DataCells">
	<xsl:param name='Properties' select = "$Class/lde:Properties"/>
	<xsl:param name="Subject"/>
	<xsl:param name="Attributes"/>

	<xsl:for-each select='$Properties/lde:Property'>
		<xsl:variable name="PropDictId" select="@dictid"/>
		<xsl:variable name="PropId" select="@id"/>

		<xsl:variable name="AttributesForProperty" select="$Attributes/lde:Attribute[@dictid=$PropDictId and @propid=$PropId]"/>

		<xsl:choose>
			<xsl:when test="lde:ComplexProperties/lde:Property">
			
				<xsl:call-template name="DataCells">
					<xsl:with-param name='Properties' select = "lde:ComplexProperties"/>
					<xsl:with-param name="Subject" select = "$Subject"/>										
					<xsl:with-param name="Attributes" select = "$AttributesForProperty/lde:ComplexAttributes"/>					
				</xsl:call-template>
			
			</xsl:when>
			
			<xsl:otherwise>
				<td>
				
					<xsl:if test="$mode='dot'">
						<xsl:call-template name="href">
							<xsl:with-param name="Id" select="$Subject/@id"/>						
						</xsl:call-template>
					</xsl:if>
				
				
				
<!--  forces the TD to not be self closing - which would fail for DOT -->
					<xsl:if test="not($AttributesForProperty/@value)">
						<xsl:text> </xsl:text>
					</xsl:if>
					<xsl:for-each select="$AttributesForProperty">
						<xsl:value-of select="@value"/><br/>
					</xsl:for-each>
				</td>
			
			</xsl:otherwise>
		
		</xsl:choose>
		
	</xsl:for-each>
	
</xsl:template>					



<xsl:template name='HeadingRow'>

	<xsl:param name='RowNum'>1</xsl:param>	

	<tr>
		<xsl:if test="$RowNum=1">
			<xsl:variable name="RowSpan">
				<xsl:value-of select="$NumberOfHeadingRows - $RowNum + 1"/>
			</xsl:variable>
			
			<th>
				<xsl:attribute name='rowspan'>
					<xsl:value-of select='$RowSpan'/>
				</xsl:attribute>	
				<xsl:text>Set</xsl:text>
			</th>
			
			<th>
				<xsl:attribute name='rowspan'>
					<xsl:value-of select='$RowSpan'/>
				</xsl:attribute>	
				<xsl:text>Subject Id</xsl:text>
			</th>
		</xsl:if>
	
		<xsl:call-template name='HeadingColumns'>
			<xsl:with-param name="RowNum" select="$RowNum"/>			
		</xsl:call-template>
	</tr>

	<xsl:if test='$RowNum &lt; $NumberOfHeadingRows'>
		<xsl:call-template name='HeadingRow'>
			<xsl:with-param name='RowNum' select='$RowNum+1'/>
		</xsl:call-template>
	</xsl:if>

	
</xsl:template>



<xsl:template name="HeadingColumns">
	<xsl:param name='RowNum'>1</xsl:param>	
	<xsl:param name='Properties' select="$Class/lde:Properties"/>
	<xsl:param name='thisRowNum'>1</xsl:param>

	<xsl:for-each select='$Properties/lde:Property'>	

		<xsl:choose>
			<xsl:when test="$thisRowNum &lt; $RowNum">
			
				<xsl:call-template name='HeadingColumns'>
					<xsl:with-param name="thisRowNum" select="$thisRowNum + 1"/>
					<xsl:with-param name="RowNum" select="$RowNum"/>
					<xsl:with-param name="Properties" select="lde:ComplexProperties"/>
				
				</xsl:call-template>
			
			</xsl:when>
			
			<xsl:otherwise>

				<xsl:variable name="ColSpan">
					<xsl:choose>
						<xsl:when test="lde:ComplexProperties/lde:Property">
							<xsl:value-of select="count(.//lde:Property)"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>				
				</xsl:variable>
		
				<xsl:variable name="RowSpan">
					<xsl:choose>
						<xsl:when test="$ColSpan=1">
							<xsl:value-of select="$NumberOfHeadingRows - $RowNum + 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>				
				</xsl:variable>
			
			
				<th>
					<xsl:attribute name='colspan'>
						<xsl:value-of select='$ColSpan'/>
					</xsl:attribute>	
					
					<xsl:attribute name='rowspan'>
						<xsl:value-of select='$RowSpan'/>
					</xsl:attribute>	
									
					<xsl:value-of select="@label"/>
				</th>
			
			</xsl:otherwise>
		
		</xsl:choose>



	</xsl:for-each>
</xsl:template>


<xsl:template name="StyleForDot">

	<table border='0' cellborder='1' cellspacing='0'>

		<xsl:variable name="Class" select="lde:Subjects/lde:Class"/>
	
		<tr>
			<td>
				<xsl:attribute name="colspan">
					<xsl:value-of select="$NumberOfCols"/>
				</xsl:attribute>
				
				<xsl:attribute name='bgcolor'>
					<xsl:value-of select="$color"/>
				</xsl:attribute>	
				
			
				<b>
					<xsl:value-of select="$Class/@heading"/>
					<br/>
					(<xsl:value-of select="$Class/@concept"/>)
				</b>
			</td>
		
		</tr>
	
		<xsl:call-template name='HeadingRowForDot'/>			
		
		<xsl:for-each select="/lde:Subjects/lde:Subject">
			<xsl:variable name="Subject" select="."/>
				
			<tr>
				
				<xsl:call-template name='DataCells'>
					<xsl:with-param name="Subject" select="$Subject"/>
					<xsl:with-param name="Attributes" select="$Subject/lde:Attributes"/>
				</xsl:call-template>
					
			</tr>
		</xsl:for-each>

	</table>

</xsl:template>	


<xsl:template name='HeadingRowForDot'>

	<xsl:param name='RowNum'>1</xsl:param>	

	<tr>
		
		<xsl:call-template name='HeadingColumnsForDot'>
			<xsl:with-param name="RowNum" select="$RowNum"/>			
		</xsl:call-template>
	</tr>

	<xsl:if test='$RowNum &lt; $NumberOfHeadingRows'>
		<xsl:call-template name='HeadingRowForDot'>
			<xsl:with-param name='RowNum' select='$RowNum+1'/>
		</xsl:call-template>
	</xsl:if>

	
</xsl:template>



<xsl:template name="HeadingColumnsForDot">
	<xsl:param name='RowNum'>1</xsl:param>	
	<xsl:param name='Properties' select="$Class/lde:Properties"/>
	<xsl:param name='thisRowNum'>1</xsl:param>

	<xsl:for-each select='$Properties/lde:Property'>	

		<xsl:choose>
			<xsl:when test="$thisRowNum &lt; $RowNum">
			
				<xsl:call-template name='HeadingColumnsForDot'>
					<xsl:with-param name="thisRowNum" select="$thisRowNum + 1"/>
					<xsl:with-param name="RowNum" select="$RowNum"/>
					<xsl:with-param name="Properties" select="lde:ComplexProperties"/>
				
				</xsl:call-template>
			
			</xsl:when>
			
			<xsl:otherwise>

				<xsl:variable name="ColSpan">
					<xsl:choose>
						<xsl:when test="lde:ComplexProperties/lde:Property">
							<xsl:value-of select="count(.//lde:Property)"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>				
				</xsl:variable>
		
				<xsl:variable name="RowSpan">
					<xsl:choose>
						<xsl:when test="$ColSpan=1">
							<xsl:value-of select="$NumberOfHeadingRows - $RowNum + 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>				
				</xsl:variable>
			
			
				<td>
					<xsl:attribute name='colspan'>
						<xsl:value-of select='$ColSpan'/>
					</xsl:attribute>	
					
					<xsl:attribute name='rowspan'>
						<xsl:value-of select='$RowSpan'/>
					</xsl:attribute>	
									
					<xsl:attribute name='bgcolor'>
						<xsl:value-of select="$color"/>
					</xsl:attribute>	
									
					<b>
						<xsl:value-of select="@label"/>
					</b>
				</td>
			
			</xsl:otherwise>
		
		</xsl:choose>



	</xsl:for-each>

</xsl:template>



<xsl:template name='href'>
	<xsl:param name='Id' select='@id'/>

	<xsl:attribute name='href'>
		<xsl:value-of select='$returnUrl'/>
		<xsl:choose>
			<xsl:when test="contains($returnUrl, '?')">
				<xsl:text>&amp;</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>?</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:value-of select="$FieldName"/>
		<xsl:text>=</xsl:text>
		<xsl:value-of select="$Id"/>
		
		<xsl:if test="not($ShapeId='')">
			<xsl:text>&amp;shapeid=</xsl:text>
			<xsl:value-of select="$ShapeId"/>
		</xsl:if>
		
	</xsl:attribute>

</xsl:template>


</xsl:stylesheet>