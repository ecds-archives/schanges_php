import os
import re
from urllib import urlencode
import logging

from django.conf import settings
from django.shortcuts import render, render_to_response
from django.http import HttpResponse, Http404
from django.core.paginator import Paginator, InvalidPage, EmptyPage, PageNotAnInteger
from django.template import RequestContext
from django.shortcuts import redirect

from schanges.models import Issue, Article, Fields, TeiDoc, Topics
from schanges.forms import SearchForm

from eulxml.xmlmap.core import load_xmlobject_from_file
from eulxml.xmlmap.teimap import Tei, TeiDiv, _TeiBase, TEI_NAMESPACE, xmlmap
from eulcommon.djangoextras.http.decorators import content_negotiation
from eulexistdb.query import escape_string
from eulexistdb.exceptions import DoesNotExist # ReturnedMultiple needed also ?

logger = logging.getLogger(__name__)

def index(request):
  return render_to_response('index.html', context_instance=RequestContext(request))

def overview(request):
  return render_to_response('overview.html', context_instance=RequestContext(request))

def searchform(request):
    "Search by keyword/author/title/article_date"
    form = SearchForm(request.GET)
    response_code = None
    context = {'searchform': form}
    search_opts = {}
    number_of_results = 20
      
    if form.is_valid():
        if 'keyword' in form.cleaned_data and form.cleaned_data['keyword']:
            search_opts['fulltext_terms'] = '%s' % form.cleaned_data['keyword']
        if 'author' in form.cleaned_data and form.cleaned_data['author']:
            search_opts['author__contains'] = '%s' % form.cleaned_data['author']
        if 'title' in form.cleaned_data and form.cleaned_data['title']:
            search_opts['head__fulltext_terms'] = '%s' % form.cleaned_data['title']
        if 'article_date' in form.cleaned_data and form.cleaned_data['article_date']:
            search_opts['date__contains'] = '%s' % form.cleaned_data['article_date']
                
        articles = Article.objects.only("id", "head", "author", "date", "issue_id").filter(**search_opts)

        searchform_paginator = Paginator(articles, number_of_results)
        
        try:
            page = int(request.GET.get('page', '1'))
        except ValueError:
            page = 1
        # If page request (9999) is out of range, deliver last page of results.
        try:
            searchform_page = searchform_paginator.page(page)
        except (EmptyPage, InvalidPage):
            searchform_page = searchform_paginator.page(paginator.num_pages)

        context['articles'] = articles
        context['articles_paginated'] = searchform_page
        context['keyword'] = form.cleaned_data['keyword']
        context['author'] = form.cleaned_data['author']
        context['title'] = form.cleaned_data['title']
        context['article_date'] = form.cleaned_data['article_date']

        context['letters'] = letters
        context['letters_paginated'] = searchbox_page
        context['keyword'] = form.cleaned_data['keyword']
           
        response = render_to_response('search_results.html', context, context_instance=RequestContext(request))
                 
        
    else:
        response = render(request, 'search.html', {"searchform": form})
       
    if response_code is not None:
        response.status_code = response_code
    return response
  

def issues(request):
  "Browse list of issues"
  context = {}
  issues = Issue.objects.only('id', 'date', 'head',).order_by('date')
  list_1 = []
  list_2 = []
  list_3 = []
  list_4 = []
  list_5 = []
  #stuff = issues.serialize()
  for issue in issues:
    year = issue.date[:4]
    if year > 1978:
      list_1.append(issue)
    else:
      pass
    if issue.date >= 1985-01-01 and issue.date < 1990-01-01:
      list_2.append(issue)
    else:
      pass
    if issue.date >= 1990-01-01 and issue.date < 1995-01-01:
      list_3.append(issue)
    else:
      pass
    if issue.date >= 1995-01-01 and issue.date < 2000-01-01:
      list_4.append(issue)
    else:
      pass
    if issue.date >= 2000-01-01 and issue.date < 2005-01-01:
      list_5.append(issue)
    else:
      pass
  context['issues'] = issues
  context['list_1'] = list_1
  context['list_2'] = list_2
  context['list_3'] = list_3
  context['list_4'] = list_4
  context['list_5'] = list_5
  context['year'] = year
  #context['stuff'] = stuff
  return render_to_response('issues.html', context, context_instance=RequestContext(request))

def topics(request):
  "See a list of topics."
  topics = Topics.objects.all()
  return render_to_response('topics.html', {'topics' : topics}, context_instance=RequestContext(request))

def topic_list(request, topic_id):
  "Browse articles in a single topic."
  topic = Topics.objects.get(id__exact=topic_id)
  extra_fields = ['issue__id']
  docs = Article.objects.also(*extra_fields).filter(ana__contains=topic_id)
  return render_to_response('topic_list.html', {'topic' : topic, 'docs' : docs}, context_instance=RequestContext(request))

def issue_toc(request, doc_id):
  "Display the contents of a single issue."
  issue = Issue.objects.get(id__exact=doc_id)
  return render_to_response('issue_toc.html', {'issue': issue,}, context_instance=RequestContext(request))

def issue_display(request, doc_id):
    "Display the contents of a single issue."
    try:
        issue = Issue.objects.get(id__exact=doc_id)
        format = issue.xsl_transform(filename=os.path.join(settings.BASE_DIR, 'xslt', 'issue.xslt'))
        return render_to_response('issue_display.html', {'issue': issue, 'format': format.serialize()}, context_instance=RequestContext(request))
    except DoesNotExist:
        raise Http404

def article_display(request, doc_id, div_id):
  "Display the contents of a single article."
  
  try:
    extra_fields = ['issue__id', 'issue__title']
    div = Article.objects.filter(issue__id=doc_id).get(id=div_id)
    body = div.xsl_transform(filename=os.path.join(settings.BASE_DIR, 'xslt', 'issue.xslt'))
    return render_to_response('article_display.html', {'div': div, 'body' : body.serialize()}, context_instance=RequestContext(request))
  except DoesNotExist:
        raise Http404


