<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:x="urn:schemas-microsoft-com:office:excel"
        xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
        xmlns="urn:schemas-microsoft-com:office:spreadsheet"
>
    <xsl:output method="xml" indent="yes" encoding="UTF-8"/>
    <xsl:variable name="vNL" select="'&#x0A;'"/>
    <xsl:variable name="vCR" select="'&#13;'"/>
    <xsl:template name="string-replace-all">
        <xsl:param name="text"/>
        <xsl:param name="replace"/>
        <xsl:param name="by"/>
        <xsl:param name="disableOutputEscaping"/>
        <xsl:choose>
            <xsl:when test="$text = '' or $replace = ''or not($replace)">
                <!-- Prevent this routine from hanging -->
                <xsl:choose>
                    <xsl:when test="$disableOutputEscaping = 'yes'">
                        <xsl:value-of select="$text" disable-output-escaping="yes"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$text"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($text, $replace)">
                <xsl:choose>
                    <xsl:when test="$disableOutputEscaping = 'yes'">
                        <xsl:value-of select="substring-before($text,$replace)" disable-output-escaping="yes"/>
                        <xsl:value-of select="$by" disable-output-escaping="yes"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before($text,$replace)"/>
                        <xsl:value-of select="$by"/>
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:call-template name="string-replace-all">
                    <xsl:with-param name="text" select="substring-after($text,$replace)"/>
                    <xsl:with-param name="replace" select="$replace"/>
                    <xsl:with-param name="by" select="$by"/>
                    <xsl:with-param name="disableOutputEscaping" select="$disableOutputEscaping"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="$disableOutputEscaping = 'yes'">
                        <xsl:value-of select="$text" disable-output-escaping="yes"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$text"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="/">
        <!--
            This template establishes the typical Workbook and Worksheet.
            Here we define the styles, the Excel metadata, Print metadata, and setup the header
        -->
        <Workbook>
            <Styles>
                <Style ss:ID="braceletHeader">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Top"/>
                    <Font ss:FontName="Verdana" ss:Bold="1" ss:Size="9" ss:Underline="Single"/>
                </Style>
                <Style ss:ID="braceletType">
                    <Font ss:FontName="Arial" ss:Underline="Single" ss:Italic="1"/>
                    <Alignment ss:Vertical="Center"/>
                </Style>
                <Style ss:ID="braceletCenter">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
                    <Protection ss:Protected="0"/>
                </Style>
                <Style ss:ID="braceletCharm">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                    <Protection ss:Protected="0"/>
                </Style>
                <Style ss:ID="braceletCenterBold">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
                    <Protection ss:Protected="0"/>
                    <Font ss:FontName="Arial" ss:Bold="1"/>
                </Style>
                <Style ss:ID="braceletEngravingText">
                    <Font ss:FontName="Verdana" ss:Size="10" ss:Bold="1"/>
                    <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
                </Style>
                <Style ss:ID="braceletEngravingTextVietnam">
                    <Font ss:FontName="Verdana" ss:Size="10" ss:Bold="1"/>
                    <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
                    <Interior ss:Color="#FFCC99" ss:Pattern="Solid"/>
                </Style>
            </Styles>
            <Worksheet ss:Name="Engravings">
                <Table>
                    <Column ss:Width="70.5"/>
                    <Column ss:Width="48.75"/>
                    <Column ss:Width="173.25"/>
                    <Column ss:Width="120"/>
                    <Column ss:Width="79.5"/>
                    <Column ss:Width="79.5"/>
                    <Column ss:Width="100"/>
                    <Column ss:Width="39"/>
                    <Row ss:Index="2">
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Order Number</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Quantity</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Engraving</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Engraving Style</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Metal Color</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Top Icon</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Charms</Data>
                        </Cell>
                        <Cell ss:StyleID="braceletHeader">
                            <Data ss:Type="String">Event</Data>
                        </Cell>
                    </Row>
                    <!--
                        Apply the per-item template and sort the items
                    -->
                    <xsl:for-each select="
/objects/object/items/item[
    not(
        custom_options/custom_option[bracelet_piece='engraving_style']/value = preceding::item[
            not(product_type='configurable')
            and product_attributes/product_type='Paracord'
        ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and not(product_type='configurable')
    and product_attributes/product_type='Paracord'
]">
                        <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                        <xsl:for-each select="
/objects/object/items/item[
    (custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and not(
        product_attributes/material = preceding::item[
            custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value
            and not(product_type='configurable')
            and product_attributes/product_type='Paracord'
        ]/product_attributes/material
    )
    and not(product_type='configurable')
    and product_attributes/product_type='Paracord'
]">

                            <Row ss:Height="26">
                                <Cell/>
                                <Cell/>
                                <Cell ss:StyleID="braceletType">
                                    <Data ss:Type="String">
                                        <xsl:value-of select="product_attributes/material"/>
                                        <xsl:text> - </xsl:text>
                                        <xsl:value-of
                                                select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>
                                    </Data>
                                </Cell>
                            </Row>

                            <xsl:apply-templates select="
/objects/object/items/item[
    (custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and (product_attributes/material = current()/product_attributes/material)
    and not(product_type='configurable')
    and product_attributes/product_type='Paracord'
]">
                                <!-- Sorts -->
                                <xsl:sort
                                        select="string-length(custom_options/custom_option[name='engraving']/value) - string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, ''))"/>
                                <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                <xsl:sort select="product_attributes/name_product/event"/>
                            </xsl:apply-templates>
                        </xsl:for-each>
                    </xsl:for-each>
                </Table>
                <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
                    <PageSetup>
                        <Layout x:Orientation="Landscape"/>
                        <Header x:Data="&amp;L&amp;D&amp;C&amp;F&amp;R&amp;P"/>
                    </PageSetup>
                    <Print>
                        <PaperSizeIndex>5</PaperSizeIndex>
                    </Print>
                </WorksheetOptions>
            </Worksheet>
        </Workbook>
    </xsl:template>


    <xsl:template match="item">
        <!--
            This template is for each item, here we render the data for all the headers per-item
        -->

        <!-- Required in order to load Name Product Data -->
        <xsl:variable name="name_id" select="product_options_data/super_product_config/product_id"/>
        <!-- Required in order to load Option Data -->
        <xsl:variable name="option_id" select="custom_options/custom_option[bracelet_piece='engraving']/option_id"/>

        <xsl:variable name="engravingStyle">
            <xsl:choose>
                <xsl:when test="product_attributes/name_product/event = 'Vietnam War'">
                    <xsl:text>braceletEngravingTextVietnam</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>braceletEngravingText</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <Row ss:Height="52">
            <Cell ss:StyleID="braceletCenter">
                <Data ss:Type="String">
                    <xsl:value-of select="normalize-space(../../increment_id)"/>
                </Data>
            </Cell>
            <Cell ss:StyleID="braceletCenterBold">
                <Data ss:Type="Number">
                    <xsl:value-of select="normalize-space(round(qty_ordered))"/>
                </Data>
            </Cell>
            <Cell ss:StyleID="{$engravingStyle}">
                <Data ss:Type="String">
                    <xsl:call-template name="string-replace-all">
                        <xsl:with-param name="text" select="custom_options/custom_option[bracelet_piece='engraving']/value" />
                        <xsl:with-param name="replace" select="$vNL" />
                        <xsl:with-param name="by" select="'&amp;#10;'" />
                        <xsl:with-param name="disableOutputEscaping" select="'yes'" />
                    </xsl:call-template>
                </Data>
            </Cell>
            <Cell ss:StyleID="braceletCenter">
                <Data ss:Type="String">
                    <xsl:value-of select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>
                </Data>
            </Cell>
            <Cell ss:StyleID="braceletCenter">
                <Data ss:Type="String">
                    <xsl:value-of select="custom_options/custom_option[bracelet_piece='material_color']/value"/>
                </Data>
            </Cell>
            <Cell ss:StyleID="braceletCenter">
                <Data ss:Type="String">
                    <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_top']/value"/>
                </Data>
            </Cell>
            <Cell ss:StyleID="braceletCharm">
                <Data ss:Type="String">
                    <xsl:for-each select="custom_options/custom_option[starts-with(bracelet_piece,'charm_')]">
                        <xsl:value-of select="value"/>
                        <xsl:if test="position() != last()">
                            <xsl:text disable-output-escaping="yes">&amp;#xa;</xsl:text>
                        </xsl:if>
                    </xsl:for-each>
                </Data>
            </Cell>
            <Cell ss:StyleID="braceletCenter">
                <Data ss:Type="String">
                    <xsl:choose>
                        <xsl:when test="product_attributes/name_product/event">
                            <xsl:value-of select="product_attributes/name_product/event"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>Custom</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </Data>
            </Cell>
        </Row>
        <!-- Create two empty spaces after each line (MBSUP-3) -->
        <Row/>
        <Row/>
    </xsl:template>
</xsl:stylesheet>
