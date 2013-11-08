#from django.conf.urls import patterns, include, url
from django.conf.urls.defaults import *
from django.conf import settings
from django.conf.urls.static import static
from django.contrib.staticfiles.urls import staticfiles_urlpatterns
from django.views.generic.simple import redirect_to

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

from schanges.views import index, overview, searchform, issues, issue_toc, issue_display, article_display, topics, topic_list

urlpatterns = patterns('schanges.views',
    url(r'^$', 'index', name='index'),
    url(r'^overview$', 'overview', name='overview'),
    url(r'^search$', 'searchform', name='search'),
    url(r'^issue$', 'issues', name='issues'),
    url(r'^topics$', 'topics', name='topics'),
    url(r'^(?P<doc_id>[^/]+)/contents$', 'issue_toc', name="issue_toc"),
    url(r'^(?P<doc_id>[^/]+)/issue$', 'issue_display', name="issue_display"),
    url(r'^(?P<doc_id>[^/]+)/(?P<div_id>[^/]+)/$', 'article_display', name='article_display'),
    url(r'^(?P<topic_id>[^/]+)/docs$', 'topic_list', name='topic_list'),
    )

if settings.DEBUG:
  urlpatterns += patterns(
    url(r'^static/(?P<path>.*)$', 'django.views.static.serve', {'document_root': settings.STATIC_ROOT } ),
)



