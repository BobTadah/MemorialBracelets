<?xml version="1.0" encoding="utf-8"?>
<?mso-application progid="Excel.Sheet"?>
<files>
    <file filename="%Y%-%m%-%d% Weaver Paracord Engraving Order.xml">
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
                            <Font ss:FontName="Verdana" ss:Bold="1" ss:Size="10" ss:Underline="Single"/>
                        </Style>
                        <Style ss:ID="braceletType">
                            <Font ss:FontName="Arial" ss:Size="10" ss:Underline="Single" ss:Italic="1"/>
                            <Alignment ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="braceletCenter">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:Size="10"/>
                        </Style>
                        <Style ss:ID="braceletCharm">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:Size="10"/>
                        </Style>
                        <Style ss:ID="braceletCenterBold">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
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
                            <Column ss:Width="79"/>
                            <Column ss:Width="45"/>
                            <Column ss:Width="367"/>
                            <Column ss:Width="62"/>
                            <Column ss:Width="62"/>
                            <Column ss:Width="40"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
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
                                    <Data ss:Type="String">Metal Color</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Paracord Color</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Size</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Top Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Left Charm</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Right Charm</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Event</Data>
                                </Cell>
                            </Row>
                            <!--
                                Apply the per-item template and sort the items
                            -->
                            <xsl:for-each select="
objects/object/items/item[
    not(
        custom_options/custom_option[bracelet_piece='size']/value = preceding::item[
            not(producttype='configurable')
            and product_attributes/producttype='Paracord'
        ]/custom_options/custom_option[bracelet_piece='size']/value
    )
    and not(producttype='configurable')
    and product_attributes/producttype='Paracord'
]">
                                <xsl:sort select="custom_options/custom_option[bracelet_piece='size']"/>
                                <xsl:sort select="additional_options/additional_option[option_label='engraving_used_qty']/value"/>
                                <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>

                                <Row ss:Height="26">
                                    <Cell/>
                                    <Cell/>
                                    <Cell ss:StyleID="braceletType">
                                        <Data ss:Type="String">
                                            <xsl:value-of select="substring-before(custom_options/custom_option[bracelet_piece='size']/value, ' ')"/>
                                            <xsl:text> - Dog Tag with 2 holes, slightly bent</xsl:text>
                                        </Data>
                                    </Cell>
                                </Row>

                                <xsl:apply-templates select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='size']/value = current()/custom_options/custom_option[bracelet_piece='size']/value
    and not(producttype='configurable')
    and product_attributes/producttype='Paracord'
]">
                                    <!-- Sorts -->
                                    <xsl:sort select="custom_options/custom_option[bracelet_piece='paracord_color']"/>
                                    <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                </xsl:apply-templates>
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
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR'
                        or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR'
                        or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE'
                        ">
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
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='material_color']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='paracord_color']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenterBold">
                        <Data ss:Type="String">
                            <!-- Remove measurement from size. -->
                            <xsl:value-of select="substring-before(custom_options/custom_option[bracelet_piece='size']/value, ' ')"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_top']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCharm">
                        <Data ss:Type="String">
                            <xsl:for-each select="custom_options/custom_option[starts-with(bracelet_piece,'charm_left')]">
                                <xsl:value-of select="value"/>
                                <xsl:if test="position() != last()">
                                    <xsl:text disable-output-escaping="yes">&amp;#xa;</xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCharm">
                        <Data ss:Type="String">
                            <xsl:for-each select="custom_options/custom_option[starts-with(bracelet_piece,'charm_right')]">
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
                                <!-- We use additional_options engraving_type as event. -->
                                <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value">
                                    <xsl:value-of select="additional_options/additional_option[option_label='engraving_type']/value"/>
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

    </file>
    <file filename="%Y%-%m%-%d% Paracord Engraving Order.xml">
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
                            <Font ss:FontName="Verdana" ss:Bold="1" ss:Size="10" ss:Underline="Single"/>
                        </Style>
                        <Style ss:ID="braceletType">
                            <Font ss:FontName="Arial" ss:Size="10" ss:Underline="Single" ss:Italic="1"/>
                            <Alignment ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="braceletCenter">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:Size="10"/>
                        </Style>
                        <Style ss:ID="braceletCenterBold">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
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
                            <Column ss:Width="79"/>
                            <Column ss:Width="45"/>
                            <Column ss:Width="367"/>
                            <Column ss:Width="140"/>
                            <Column ss:Width="62"/>
                            <Column ss:Width="145"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
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
            not(producttype='configurable')
            and product_attributes/producttype='Paracord'
        ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and not(producttype='configurable')
    and product_attributes/producttype='Paracord'
]">
                                <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                <xsl:for-each select="
/objects/object/items/item[
    (custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and not(
        product_attributes/material = preceding::item[
            custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value
            and not(producttype='configurable')
            and product_attributes/producttype='Paracord'
        ]/product_attributes/material
    )
    and not(producttype='configurable')
    and product_attributes/producttype='Paracord'
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
                                                <xsl:text> - Dog Tag with 2 holes, slightly bent</xsl:text>
                                            </Data>
                                        </Cell>
                                    </Row>

                                    <xsl:apply-templates select="
/objects/object/items/item[
    (custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and (product_attributes/material = current()/product_attributes/material)
    and not(producttype='configurable')
    and product_attributes/producttype='Paracord'
]">
                                        <!-- Sorts -->
                                        <xsl:sort
                                                select="string-length(custom_options/custom_option[name='engraving']/value) - string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, ''))"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                        <!-- We use additional_options engraving_type as event. -->
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>
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
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR'
                        or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR'
                        or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE'
                        ">
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
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:choose>
                                <!-- We use additional_options engraving_type as event. -->
                                <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value">
                                    <xsl:value-of select="additional_options/additional_option[option_label='engraving_type']/value"/>
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

    </file>
    <file filename="%Y%-%m%-%d% Engraving Order - Recessed.xml">
        <xsl:stylesheet
                version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                xmlns:x="urn:schemas-microsoft-com:office:excel"
                xmlns="urn:schemas-microsoft-com:office:spreadsheet"
        >
            <xsl:output method="xml" indent="yes" encoding="UTF-8"/>

            <!-- Newline character -->
            <xsl:variable name="vNL" select="'&#x0A;'"/>
            <!-- Carriage Return character -->
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

            <!--
            Throughout this file you'll find for-eachs where it has a bunch of ands, then a not with a bunch of ands in it.

            This is the structure we use for creating a hierarchy in 2D space.  In particular, these are responsible for
            reducing all of the individual order items down to a unique array of an attribute, for example: every bracelet width
            once and only once.  Once we've done that filter, we'll have a current() item that we can use as the basis for the
            children filters.
            -->

            <xsl:template match="/">
                <!--
                    This template establishes the typical Workbook and Worksheet.
                    Here we define the styles, the Excel metadata, Print metadata, and setup the header
                -->
                <Workbook>
                    <Styles>
                        <Style ss:ID="braceletHeader">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Top"/>
                            <Font ss:FontName="Verdana" ss:Bold="1" ss:Size="10" ss:Underline="Single"/>
                        </Style>
                        <Style ss:ID="braceletType">
                            <Font ss:FontName="Arial" ss:Size="10" ss:Underline="Single" ss:Italic="1"/>
                            <Alignment ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="braceletCenter">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:Size="10"/>
                        </Style>
                        <Style ss:ID="braceletCenterBold">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
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
                        <Style ss:ID="braceletEngravingTextStyleHalf">
                            <Font ss:FontName="Verdana" ss:Size="10" ss:Bold="1"/>
                            <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Interior ss:Color="#00B2FF" ss:Pattern="Solid"/>
                        </Style>
                    </Styles>
                    <Worksheet ss:Name="Engravings">
                        <Table>
                            <Column ss:Width="79"/>
                            <Column ss:Width="45"/>
                            <Column ss:Width="367"/>
                            <Column ss:Width="95"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="62"/>
                            <Column ss:Width="40"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
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
                                    <Data ss:Type="String">Material</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Metal Color</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Size</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Left Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Right Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Top Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Event</Data>
                                </Cell>
                            </Row>
                            <!--
                                Apply the per-item template and sort the items

                                1. is valid item
                                2. is not vietnam
                                3. has unique width (must contain above)
                            -->
                            <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and not(product_attributes/bracelet_width = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))
    ]/product_attributes/bracelet_width)
]">
                                <xsl:sort select="product_attributes/bracelet_width" order="descending"/>
                                <xsl:variable name="braceletWidth" select="current()/product_attributes/bracelet_width"/>

                                <!--
                                    For every different type of engraving style
                                    1. is valid item
                                    2. is not vietnam
                                    3. matches width
                                    4. is unique engraving style
                                -->
                                <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and product_attributes/bracelet_width = $braceletWidth

    and not(custom_options/custom_option[bracelet_piece='engraving_style']/value = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

        and product_attributes/bracelet_width = $braceletWidth
    ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
]">
                                    <!-- Sort engraving styles (applies to parent for-each) -->
                                    <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                    <xsl:variable name="engravingStyle" select="current()/custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                    <!--
                                    1. is valid item
                                    2. is not vietnam
                                    3. matches width
                                    4. matches engraving style
                                    5. unique material
                                    -->
                                    <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and product_attributes/bracelet_width = $braceletWidth

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle

    and not(product_attributes/material = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

        and product_attributes/bracelet_width = $braceletWidth

        and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle
    ]/product_attributes/material)
]">
                                        <xsl:sort select="current()/product_attributes/material"/>
                                        <xsl:variable name="material" select="current()/product_attributes/material"/>
                                        <xsl:variable name="producttype" select="current()/product_attributes/producttype"/>

                                        <Row ss:Height="26">
                                            <Cell/>
                                            <Cell/>
                                            <Cell ss:StyleID="braceletType">
                                                <Data ss:Type="String">
                                                    <xsl:choose>
                                                        <!-- Make sure we don't add bracelet width in Dog Tags. -->
                                                        <xsl:when test="$producttype != 'Dog Tag'">
                                                            <xsl:value-of select="$braceletWidth"/>
                                                        </xsl:when>
                                                    </xsl:choose>
                                                </Data>
                                            </Cell>
                                        </Row>

                                        <Row ss:Height="26">
                                            <Cell/>
                                            <Cell/>
                                            <Cell ss:StyleID="braceletType">
                                                <Data ss:Type="String">
                                                    <xsl:value-of select="$material"/>
                                                </Data>
                                            </Cell>
                                        </Row>

                                        <!--
                                        1. is valid item
                                        2. not vietnam
                                        3. match width
                                        4. match engraving style
                                        5. match material
                                        -->
                                        <xsl:apply-templates select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and product_attributes/bracelet_width = $braceletWidth

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle

    and product_attributes/material = $material
]">
                                            <!-- Sorts -->
                                            <xsl:sort select="
string(
    string-length(custom_options/custom_option[name='engraving']/value)
    -
    string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, ''))
)"/> <!-- Lines in Engraving -->
                                            <xsl:sort select="additional_options/additional_option[option_label='engraving_used_qty']/value"/>
                                            <xsl:sort select="custom_options/custom_option[bracelet_piece='size']"/>
                                            <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>
                                            <xsl:sort select="custom_options/custom_option[bracelet_piece='paracord_color']"/>
                                            <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                        </xsl:apply-templates>

                                    </xsl:for-each>

                                </xsl:for-each>
                            </xsl:for-each>

                            <xsl:if test="(count(
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')
    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' )
]
                    ) > 0)">
                            </xsl:if>

                            <!--
                            1. is valid item
                            2. is vietnam
                            3. unique engraving style
                            -->
                            <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')

    and not(custom_options/custom_option[bracelet_piece='engraving_style']/value = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')
    ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
]">
                                <!-- Sort engraving styles (applies to parent for-each) -->
                                <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                <xsl:variable name="engravingStyle" select="current()/custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                <xsl:function name="notConfigurable">
                                    <xsl:param name="item"/>
                                    <xsl:sequence select="not($item/producttype='configurable')"/>
                                </xsl:function>

                                <!--
                                1. valid item
                                2. vietnam
                                3. match engraving style
                                4. unique material
                                -->
                                <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle

    and not(product_attributes/material = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')

        and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle
    ]/product_attributes/material)
]">
                                    <xsl:sort select="current()/product_attributes/material"/>
                                    <xsl:variable name="material" select="current()/product_attributes/material"/>

                                    <Row ss:Height="26">
                                        <Cell/>
                                        <Cell/>
                                        <Cell ss:StyleID="braceletType">
                                            <Data ss:Type="String">
                                                <xsl:text>Vietnam</xsl:text>
                                            </Data>
                                        </Cell>
                                    </Row>
                                    <Row>
                                        <Cell/>
                                        <Cell/>
                                        <Cell ss:StyleID="braceletType">
                                            <Data ss:Type="String">
                                                <xsl:text>*** 1/2" band; 3rd line = Ends of POW Bracelet ***</xsl:text>
                                            </Data>
                                        </Cell>
                                    </Row>
                                    <Row ss:Height="26">
                                        <Cell/>
                                        <Cell/>
                                        <Cell ss:StyleID="braceletType">
                                            <Data ss:Type="String">
                                                <xsl:value-of select="$material"/>
                                            </Data>
                                        </Cell>
                                    </Row>

                                    <!--
                                    1. valid item
                                    2. vietnam
                                    3. match engraving style
                                    4. match material
                                    -->
                                    <xsl:apply-templates select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Recessed Engraved'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')
    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')
    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle
    and product_attributes/material = $material
]">
                                        <!-- Sorts -->
                                        <xsl:sort
                                                select="string(string-length(custom_options/custom_option[name='engraving']/value) - string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, '')))"/>
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_used_qty']/value"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='size']"/>
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='paracord_color']"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
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

                <xsl:variable name="braceletWidth" select="product_attributes/bracelet_width"/>

                <xsl:variable name="engravingStyle">
                    <xsl:choose>
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ">
                            <xsl:text>braceletEngravingTextVietnam</xsl:text>
                        </xsl:when>
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="$braceletWidth = '1/2&quot;'">
                            <xsl:text>braceletEngravingTextStyleHalf</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>braceletEngravingText</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

                <!--<xsl:if test="(additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')">-->
                <!--<Row>-->
                <!--<Cell/>-->
                <!--<Cell/>-->
                <!--<Cell ss:StyleID="braceletType">-->
                <!--<Data ss:Type="String">-->
                <!--<xsl:text>*** 1/2" band; 3rd line = Ends of POW Bracelet ***</xsl:text>-->
                <!--</Data>-->
                <!--</Cell>-->
                <!--</Row>-->
                <!--</xsl:if>-->

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
                                <xsl:with-param name="text" select="custom_options/custom_option[bracelet_piece='engraving']/value"/>
                                <xsl:with-param name="replace" select="$vNL"/>
                                <xsl:with-param name="by" select="'&amp;#10;'"/>
                                <xsl:with-param name="disableOutputEscaping" select="'yes'"/>
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
                            <xsl:value-of select="product_attributes/material"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='material_color']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenterBold">
                        <Data ss:Type="String">
                            <!-- Remove measurement from size. -->
                            <xsl:value-of select="substring-before(custom_options/custom_option[bracelet_piece='size']/value, ' ')"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_left']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenterBold">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_right']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_top']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="additional_options/additional_option[option_label='engraving_type']/value"/>
                        </Data>
                    </Cell>
                </Row>
                <!-- Create two empty spaces after each line (MBSUP-3) -->
                <Row/>
                <Row/>
            </xsl:template>
        </xsl:stylesheet>

    </file>
    <file filename="%Y%-%m%-%d% Engraving Order - Laser.xml">
        <xsl:stylesheet
                version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                xmlns:x="urn:schemas-microsoft-com:office:excel"
                xmlns="urn:schemas-microsoft-com:office:spreadsheet"
        >
            <xsl:output method="xml" indent="yes" encoding="UTF-8"/>

            <!-- Newline character -->
            <xsl:variable name="vNL" select="'&#x0A;'"/>
            <!-- Carriage Return character -->
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

            <!--
            Throughout this file you'll find for-eachs where it has a bunch of ands, then a not with a bunch of ands in it.

            This is the structure we use for creating a hierarchy in 2D space.  In particular, these are responsible for
            reducing all of the individual order items down to a unique array of an attribute, for example: every bracelet width
            once and only once.  Once we've done that filter, we'll have a current() item that we can use as the basis for the
            children filters.
            -->

            <xsl:template match="/">
                <!--
                    This template establishes the typical Workbook and Worksheet.
                    Here we define the styles, the Excel metadata, Print metadata, and setup the header
                -->
                <Workbook>
                    <Styles>
                        <Style ss:ID="braceletHeader">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Top"/>
                            <Font ss:FontName="Verdana" ss:Bold="1" ss:Size="10" ss:Underline="Single"/>
                        </Style>
                        <Style ss:ID="braceletType">
                            <Font ss:FontName="Arial" ss:Size="10" ss:Underline="Single" ss:Italic="1"/>
                            <Alignment ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="braceletCenter">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:Size="10"/>
                        </Style>
                        <Style ss:ID="braceletCenterBold">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
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
                        <Style ss:ID="braceletEngravingTextStyleHalf">
                            <Font ss:FontName="Verdana" ss:Size="10" ss:Bold="1"/>
                            <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Interior ss:Color="#00B2FF" ss:Pattern="Solid"/>
                        </Style>
                    </Styles>
                    <Worksheet ss:Name="Engravings">
                        <Table>
                            <Column ss:Width="79"/>
                            <Column ss:Width="45"/>
                            <Column ss:Width="367"/>
                            <Column ss:Width="140"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="62"/>
                            <Column ss:Width="40"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
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
                                    <Data ss:Type="String">Material</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Metal Color</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Size</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Left Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Right Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Top Icon</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Event</Data>
                                </Cell>
                            </Row>
                            <!--
                            1. isValidItem
                            2. isNotVietnamBracelet
                            3. is a Unique Width (must contain the above)
                            -->
                            <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and not(product_attributes/bracelet_width = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))
    ]/product_attributes/bracelet_width)
]">
                                <xsl:sort select="product_attributes/bracelet_width" order="descending"/>
                                <xsl:variable name="braceletWidth" select="current()/product_attributes/bracelet_width"/>

                                <!-- For every different type of engraving style -->
                                <!--
                                1. isValidItem
                                2. isNotVietnamBracelet
                                3. matches the previous width
                                4. is a unique engraving style (must contain above)
                                -->
                                <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and product_attributes/bracelet_width=$braceletWidth

    and not(custom_options/custom_option[bracelet_piece='engraving_style']/value = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

        and product_attributes/bracelet_width=$braceletWidth
    ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
]">
                                    <!-- Sort engraving styles (applies to parent for-each) -->
                                    <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                    <xsl:variable name="engravingStyle" select="current()/custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                    <!--
                                    1. isValidItem
                                    2. isNotVietnamBracelet
                                    3. matches the previous width
                                    4. matches the previous engraving style
                                    5. is a unique material (must contain above)
                                    -->
                                    <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and product_attributes/bracelet_width=$braceletWidth

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle

    and not(product_attributes/material = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

        and product_attributes/bracelet_width=$braceletWidth

        and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle
    ]/product_attributes/material)
]">
                                        <xsl:sort select="current()/product_attributes/material"/>
                                        <xsl:variable name="material" select="current()/product_attributes/material"/>
                                        <xsl:variable name="producttype" select="current()/product_attributes/producttype"/>

                                        <Row ss:Height="26">
                                            <Cell/>
                                            <Cell/>
                                            <Cell ss:StyleID="braceletType">
                                                <Data ss:Type="String">
                                                    <xsl:choose>
                                                        <!-- Make sure we don't add bracelet width in Dog Tags. -->
                                                        <xsl:when test="$producttype != 'Dog Tag'">
                                                            <xsl:value-of select="$braceletWidth"/>
                                                        </xsl:when>
                                                    </xsl:choose>
                                                </Data>
                                            </Cell>
                                        </Row>

                                        <Row ss:Height="26">
                                            <Cell/>
                                            <Cell/>
                                            <Cell ss:StyleID="braceletType">
                                                <Data ss:Type="String">
                                                    <xsl:value-of select="$material"/>
                                                </Data>
                                            </Cell>
                                        </Row>

                                        <!--
                                        1. is valid item
                                        2. is not vietnam
                                        3. matches width
                                        4. matches engraving
                                        5. matches material
                                        -->
                                        <xsl:apply-templates select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and not((additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag'))

    and product_attributes/bracelet_width=$braceletWidth

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle

    and product_attributes/material = $material
]">
                                            <!-- Sorts -->
                                            <xsl:sort select="
string(
    string-length(custom_options/custom_option[name='engraving']/value)
    -
    string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, ''))
)"/> <!-- Lines in Engraving -->
                                            <xsl:sort select="additional_options/additional_option[option_label='engraving_used_qty']/value"/>
                                            <xsl:sort select="custom_options/custom_option[bracelet_piece='size']"/>
                                            <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>
                                            <xsl:sort select="custom_options/custom_option[bracelet_piece='paracord_color']"/>
                                            <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                        </xsl:apply-templates>

                                    </xsl:for-each>

                                </xsl:for-each>
                            </xsl:for-each>

                            <xsl:if test="(count(
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')
    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' )]) > 0)">
                            </xsl:if>

                            <!--
                            1. is valid item
                            2. is vietnam
                            3. has unique engraving style
                            -->
                            <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')

    and not(custom_options/custom_option[bracelet_piece='engraving_style']/value = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')
    ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
]">
                                <!-- Sort engraving styles (applies to parent for-each) -->
                                <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                <xsl:variable name="engravingStyle" select="current()/custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                <xsl:function name="notConfigurable">
                                    <xsl:param name="item"/>
                                    <xsl:sequence select="not($item/producttype='configurable')"/>
                                </xsl:function>

                                <!--
                                1. is valid item
                                2. is vietnam
                                3. matches engraving style
                                4. has unique material
                                -->
                                <xsl:for-each select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle

    and not(product_attributes/material = preceding::item[
        custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
        and not(producttype='configurable')
        and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')

        and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')

        and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle]/product_attributes/material)
]">
                                    <xsl:sort select="current()/product_attributes/material"/>
                                    <xsl:variable name="material" select="current()/product_attributes/material"/>

                                    <Row ss:Height="26">
                                        <Cell/>
                                        <Cell/>
                                        <Cell ss:StyleID="braceletType">
                                            <Data ss:Type="String">
                                                <xsl:text>Vietnam</xsl:text>
                                            </Data>
                                        </Cell>
                                    </Row>
                                    <Row>
                                        <Cell/>
                                        <Cell/>
                                        <Cell ss:StyleID="braceletType">
                                            <Data ss:Type="String">
                                                <xsl:text>*** 1/2" band; 3rd line = Ends of POW Bracelet ***</xsl:text>
                                            </Data>
                                        </Cell>
                                    </Row>
                                    <Row ss:Height="26">
                                        <Cell/>
                                        <Cell/>
                                        <Cell ss:StyleID="braceletType">
                                            <Data ss:Type="String">
                                                <xsl:value-of select="$material"/>
                                            </Data>
                                        </Cell>
                                    </Row>

                                    <!--
                                    1. is valid item
                                    2. is vietnam
                                    3. matches engraving style
                                    4. matches material
                                    -->
                                    <xsl:apply-templates select="
/objects/object/items/item[
    custom_options/custom_option[bracelet_piece='engraving_style']/value = 'Laser Engraved Black Letters'
    and not(producttype='configurable')
    and (product_attributes/producttype='Dog Tag' or product_attributes/producttype='Other Engraveable')
    and (additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ) and not(product_attributes/producttype='Dog Tag')
    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle
    and product_attributes/material = $material
]">
                                        <!-- Sorts -->
                                        <xsl:sort
                                                select="string(string-length(custom_options/custom_option[name='engraving']/value) - string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, '')))"/>
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_used_qty']/value"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='size']"/>
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='paracord_color']"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                    </xsl:apply-templates>

                                </xsl:for-each>
                            </xsl:for-each>
                        </Table>
                        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
                            <PageSetup>
                                <Layout x:Orientation="Landscape"/>
                                <!-- L/C/R - Left, Center, Right -->
                                <!-- D: Date, F: Filename, P: Page number -->
                                <Header x:Data="&amp;L&amp;D&amp;C&amp;F&amp;R&amp;P"/>
                            </PageSetup>
                            <Print>
                                <!-- Legal size -->
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

                <xsl:variable name="braceletWidth" select="product_attributes/bracelet_width"/>

                <xsl:variable name="engravingStyle">
                    <xsl:choose>
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE' ">
                            <xsl:text>braceletEngravingTextVietnam</xsl:text>
                        </xsl:when>
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="$braceletWidth = '1/2&quot;'">
                            <xsl:text>braceletEngravingTextStyleHalf</xsl:text>
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
                                <xsl:with-param name="text" select="custom_options/custom_option[bracelet_piece='engraving']/value"/>
                                <xsl:with-param name="replace" select="$vNL"/>
                                <xsl:with-param name="by" select="'&amp;#10;'"/>
                                <xsl:with-param name="disableOutputEscaping" select="'yes'"/>
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
                            <xsl:value-of select="product_attributes/material"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='material_color']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenterBold">
                        <Data ss:Type="String">
                            <!-- Remove measurement from size. -->
                            <xsl:value-of select="substring-before(custom_options/custom_option[bracelet_piece='size']/value, ' ')"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_left']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenterBold">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_right']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_top']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="additional_options/additional_option[option_label='engraving_type']/value"/>
                        </Data>
                    </Cell>
                </Row>
                <!-- Create two empty spaces after each line (MBSUP-3) -->
                <Row/>
                <Row/>
            </xsl:template>
        </xsl:stylesheet>

    </file>
    <file filename="%Y%-%m%-%d% Black Leather Overlay Engravings.xml">
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
                            <Font ss:FontName="Verdana" ss:Bold="1" ss:Size="10" ss:Underline="Single"/>
                        </Style>
                        <Style ss:ID="braceletType">
                            <Font ss:FontName="Arial" ss:Size="10" ss:Underline="Single" ss:Italic="1"/>
                            <Alignment ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="braceletCenter">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:Size="10"/>
                        </Style>
                        <Style ss:ID="braceletCenterBold">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
                            <Protection ss:Protected="0"/>
                            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
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
                            <Column ss:Width="79"/>
                            <Column ss:Width="45"/>
                            <Column ss:Width="367"/>
                            <Column ss:Width="140"/>
                            <Column ss:Width="62"/>
                            <Column ss:Width="145"/>
                            <Column ss:Width="45"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="100"/>
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
                                    <Data ss:Type="String">Material</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Metal Color</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Size</Data>
                                </Cell>
                                <Cell ss:StyleID="braceletHeader">
                                    <Data ss:Type="String">Top Icon</Data>
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
            not(producttype='configurable')
            and product_attributes/producttype='Black Leather Bracelet'
        ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and not(producttype='configurable')
    and product_attributes/producttype='Black Leather Bracelet'
]">
                                <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>

                                <xsl:for-each select="
/objects/object/items/item[
    (custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and not(
        product_attributes/material = preceding::item[
            custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value
            and not(producttype='configurable')
            and product_attributes/producttype='Black Leather Bracelet'
        ]/product_attributes/material
    )
    and not(producttype='configurable')
    and product_attributes/producttype='Black Leather Bracelet'
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
                                                <xsl:text> - Dog Tag with 2 holes, slightly bent</xsl:text>
                                            </Data>
                                        </Cell>
                                    </Row>

                                    <xsl:apply-templates select="
/objects/object/items/item[
    (custom_options/custom_option[bracelet_piece='engraving_style']/value = current()/custom_options/custom_option[bracelet_piece='engraving_style']/value)
    and (product_attributes/material = current()/product_attributes/material)
    and not(producttype='configurable')
    and product_attributes/producttype='Black Leather Bracelet'
]">
                                        <!-- Sorts -->
                                        <xsl:sort
                                                select="string(string-length(custom_options/custom_option[name='engraving']/value) - string-length(translate(string(custom_options/custom_option[name='engraving']/value), $vNL, '')))"/>
                                        <!-- Lines in Engraving -->
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_used_qty']/value"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='metal_color']"/>
                                        <xsl:sort select="custom_options/custom_option[bracelet_piece='size']"/>
                                        <!-- We use additional_options engraving_type as event. -->
                                        <xsl:sort select="additional_options/additional_option[option_label='engraving_type']/value"/>
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
                        <!-- Decides engravingStyle based on Vietnam war events.-->
                        <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR'
                        or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR PRISONER OF WAR'
                        or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR MISSING IN ACTION' or additional_options/additional_option[option_label='engraving_type']/value = 'VIETNAM WAR RETURNEE'
                        ">
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
                            <xsl:value-of select="product_attributes/material"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='material_color']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenterBold">
                        <Data ss:Type="String">
                            <!-- Remove measurement from size. -->
                            <xsl:value-of select="substring-before(custom_options/custom_option[bracelet_piece='size']/value, ' ')"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:value-of select="custom_options/custom_option[bracelet_piece='icon_top']/value"/>
                        </Data>
                    </Cell>
                    <Cell ss:StyleID="braceletCenter">
                        <Data ss:Type="String">
                            <xsl:choose>
                                <!-- We use additional_options engraving_type as event. -->
                                <xsl:when test="additional_options/additional_option[option_label='engraving_type']/value">
                                    <xsl:value-of select="additional_options/additional_option[option_label='engraving_type']/value"/>
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

    </file>
</files>