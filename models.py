import re
import datetime

from django.utils.safestring import mark_safe
from django.db import models
from eulexistdb.manager import Manager
from eulexistdb.models import XmlModel
from eulxml.xmlmap.core import XmlObject 
#from eulxml.xmlmap.dc import DublinCore
from eulxml.xmlmap.fields import StringField, NodeField, StringListField, NodeListField, IntegerField
from eulxml.xmlmap.teimap import Tei, TeiDiv, _TeiBase, TEI_NAMESPACE, xmlmap, TeiInterpGroup, TeiInterp

class Fields(_TeiBase):
    ROOT_NAMESPACES = {
        'tei' : TEI_NAMESPACE,
        'xml' : 'http://www.w3.org/XML/1998/namespace'}
    id = StringField('@xml:id')
    head = StringField('tei:head')
    author = StringField("tei:byline//tei:sic")
    type = StringField("@type") 
    num = StringField("@n")
    pages = StringField("tei:docDate")
    ana = StringField("@ana")
    
class TeiDoc(Tei):
    divs = xmlmap.NodeListField('//tei:div2', Fields)

class Issue(XmlModel, Tei):
    ROOT_NAMESPACES = {'tei' : TEI_NAMESPACE}
    objects = Manager('/tei:TEI')
    divs = NodeListField('//tei:div2', Fields)
    date = StringField('tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl/tei:date/@when')
    head = StringField('//tei:div1/tei:head')

class Article(XmlModel, TeiDiv):
    ROOT_NAMESPACES = {'tei' : TEI_NAMESPACE}
    objects = Manager("//tei:div2")
    article = NodeField("//tei:div2", "self")
    id = StringField('@xml:id')
    date = StringField('tei:docDate/@when')
    head = StringField('tei:head')
    author = StringField("tei:byline//tei:sic")
    type = StringField("@type")
    pages = StringField("tei:docDate")
    issue = NodeField('ancestor::tei:TEI', Issue)
    issue_id = NodeField('ancestor::tei:TEI/@xml:id', Issue)
    ana = StringField("@ana", "self") 
   
class Topics(XmlModel):
    objects = Manager("//interp")
    id = StringField('@xml:id')
    name = StringField("//interp", "self")
    

    
   

    
    

