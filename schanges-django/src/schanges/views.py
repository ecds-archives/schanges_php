import os
from urllib import urlencode

from django.conf import settings
from django.shortcuts import render_to_response
from django.http import HttpResponse, Http404
from django.core.paginator import Paginator, InvalidPage, EmptyPage
from django.template import RequestContext

from schanges.models import IssueTitle, Article, Bibliography, SourceDescription

from eulcommon.djangoextras.http.decorators import content_negotiation
from eulexistdb.query import escape_string
from eulexistdb.exceptions import DoesNotExist # ReturnedMultiple needed also ?
 
def issues(request):
  "Browse list of issues"
  issues = IssueTitle.objects.only('id', 'title', 'date').order_by('date')
  return render_to_response('issues.html', {'issues' : issues})
  context_instance=RequestContext(request)

def issue_links(request, doc_id):
  # Temporary page that provides links to displaying the full issue and displaying one div.  Ultimately should be replaced by list of divs when article_table is made fully functional.
  issue = IssueTitle.objects.get(id__exact=doc_id)
  return render_to_response('issue_links.html', {'issue' : issue})
  context_instance=RequestContext(request)

def issue_display(request, doc_id):
    "Display the contents of a single issue."
    try:
        issue = IssueTitle.objects.get(id__exact=doc_id)
        #article = IssueTitle.objects.get(article__id=div2_id)
        format = issue.xsl_transform(filename=os.path.join(settings.BASE_DIR, 'xslt', 'issue.xslt'))
        return render_to_response('issue_display.html', {'issue': issue, 'article': article, 'format': format.serialize()}, context_instance=RequestContext(request))
    except DoesNotExist:
        raise Http404

def article_table(request, doc_id):
  # Currently not functional.  Div_id not working.
    "Display list of articles in an issue."
    issue = IssueTitle.objects.get(id__exact=doc_id)
    #article = Article.objects.get(id__exact=doc_id)
    #extra_fields = ['issue__id', 'issue__title', 'nextdiv__id', 'nextdiv__title','prevdiv__id', 'prevdiv__title', 'issue__source']
    #div = Article.objects.also(*extra_fields).filter(issue__id=doc_id, **filter).get(article__id=div2_id)
    #body = div.xsl_transform(filename=os.path.join(settings.BASE_DIR, 'poetry', 'xslt', 'div.xsl'))
    return render_to_response('article_table.html', {'issue': issue, 'article': article}, context_instance=RequestContext(request))
    
def article(request, doc_id, div2_id):
      "Display a single article"



 
  

