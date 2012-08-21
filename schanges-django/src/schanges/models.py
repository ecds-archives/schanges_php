from django.utils.safestring import mark_safe

from eulexistdb.manager import Manager
from eulexistdb.models import XmlModel
from eulxml.xmlmap.core import XmlObject
#from eulxml.xmlmap.dc import DublinCore
from eulxml.xmlmap.fields import StringField, NodeField, StringListField, NodeListField
from eulxml.xmlmap.teimap import Tei, TeiDiv, TEI_NAMESPACE

class Bibliography(XmlObject):
    ROOT_NAMESPACES = {'tei' : TEI_NAMESPACE}
    # TODO: handle repeating elements
    title = StringField('tei:title')
    author = StringField('tei:author')
    editor = StringField('tei:editor')
    publisher = StringField('tei:publisher')
    pubplace = StringField('tei:pubPlace')
    date = StringField('tei:date')

    def formatted_citation(self):
        """Generate an HTML formatted citation."""
        cit = {
            "author": '',
            "editor": '',
            "title": self.title,
            "pubplace": self.pubplace,
            "publisher":  self.publisher,
            "date": self.date
        }
        if self.author:
            cit['author'] = '%s. ' % self.author
        if self.editor:
            cit['editor'] = '%s, ed. ' % self.editor

        return mark_safe('%(author)s%(editor)s<i>%(title)s</i>. %(pubplace)s: %(publisher)s, %(date)s.' \
                % cit)


class SourceDescription(XmlObject):
    'XmlObject for TEI Source Description (sourceDesc element).'
    ROOT_NAMESPACES = {'tei' : TEI_NAMESPACE}
    bibl = NodeField('tei:bibl', Bibliography)
    ':class:`Bibliography` - `@bibl`'

    def citation(self):
        'Shortcut for :meth:`Bibligraphy.formatted_citation` to render source bibl'
        return self.bibl.formatted_citation()

class IssueTitle(XmlModel, Tei):
    ROOT_NAMESPACES = {'tei' : TEI_NAMESPACE}
    objects = Manager('/tei:TEI')
    text = StringField('tei:text')
    date =  StringField('tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title/tei:date/@when')
    author =  StringField('tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:author/tei:name/tei:choice/tei:reg')
    article = NodeField("//tei:div2[1]", "self")
    nextdiv = NodeField("following-sibling::tei:div2[1]", "self")
    prevdiv = NodeField("preceding-sibling::tei:div2[1]", "self")
    article_title = StringField("//tei:div2[1]/tei:head")
    article_author = StringField("//tei:div2[1]/tei:byline/tei:docAuthor/tei:name/tei:choice/tei:sic")
    article_type = StringField("//tei:div2[1]/@type") 
    article_num = StringField("//tei:div2[1]/@n")
    article_pages = StringField("//tei:div2[1]/tei:docDate")
    
    site_url = 'http://beck.library.emory.edu/yjallen'
    project_desc = StringField('tei:teiHeader/tei:encodingDesc/tei:projectDesc')
    geo_coverage = StringField('tei:teiHeader/tei:profileDesc/tei:creation/tei:rs[@type="geography"]')
    creation_date = StringField('tei:teiHeader/tei:profileDesc/tei:creation/tei:date')
    lcsh_subjects = StringListField('tei:teiHeader//tei:keywords[@scheme="#lcsh"]/tei:list/tei:item')
    identifier_ark = StringField('tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type="ark"]')
    source = StringField('tei:teiHeader/tei:fileDesc/tei:sourceDesc')

    @property
    def dublin_core(self):
        dc = DublinCore()
        dc.title = self.title
        dc.creator_list.extend([n.reg for n in self.header.author_list])
        dc.contributor_list.extend([n.reg for n in self.header.editor_list])
        dc.publisher = self.header.publisher
        dc.date = self.header.publication_date
        dc.rights = self.header.availability
        dc.source = self.header.source_description
        dc.subject_list.extend(self.lcsh_subjects)
        dc.description = self.project_desc

        dc.identifier = self.identifier_ark


        if self.geo_coverage:
            dc.coverage_list.append(self.geo_coverage)
        if self.creation_date:
            dc.coverage_list.append(self.creation_date)

        if self.header.series_statement:
            dc.relation_list.append(self.header.series_statement)
        # FIXME: should we also include url? site name & url are currently
        # hard-coded when setting dc:relation in postcard ingest

        return dc

class Article(XmlModel, TeiDiv):
    ROOT_NAMESPACES = {'tei' : TEI_NAMESPACE}
    
    issue = NodeField('ancestor::tei:TEI', IssueTitle)
    article = NodeField("//tei:div2", "self")
    nextdiv = NodeField("following-sibling::tei:div2[1]", "self")
    prevdiv = NodeField("preceding-sibling::tei:div2[1]", "self")
    article_title = StringField("//tei:div2/tei:head")
    article_author = StringField("//tei:div2/tei:byline/tei:docAuthor/tei:name/tei:choice/tei:sic")
    article_type = StringField("//tei:div2/@type")
    article_num = StringField("//tei:div2/@n")
    article_id = StringField("//tei:div2/@xml:id")
    article_pages = StringField("//tei:div2/tei:docDate")

    
    objects = Manager("//tei:div2")



    
    #objects = Manager("tei:div2[@type='Article' or @type='Review' or @type='List']", "self")
    #div2 = NodeField("//tei:div2[@type='Article' or @type='Review' or @type='List']", "self")
    #author = StringField("tei:div2/tei:head/tei:name/tei:choice/tei:sic")
    # article_type = StringField("tei:div2/tei:@type") 
    #article_pages = StringField("tei:div2/tei:docDate")
     
