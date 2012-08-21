from django.conf.urls.defaults import *
from django.conf import settings
from django.conf.urls.static import static
from django.contrib.staticfiles.urls import staticfiles_urlpatterns

from django.contrib import admin
admin.autodiscover()

from schanges.views import issues, issue_links, issue_display,article_table

urlpatterns = patterns('schanges.views',
    url(r'^$', 'issues', name='issues'),
    url(r'^(?P<doc_id>[^/]+)/$', 'issue_links', name="issue_links"),
    url(r'^(?P<doc_id>[^/]+)/issue$', 'issue_display', name="issue_display"),
    url(r'^(?P<doc_id>[^/]+)/article$', 'article_table', name='article_table'),
    url(r'^(?P<doc_id>[^/]+)$', 'article', name='article')
    )

#if settings.DEBUG:
  #urlpatterns += staticfiles_urlpatterns(
       #url(r'^static/(?P<path>.*)$', 'django.views.static.serve', {'document_root': settings.STATIC_ROOT } ),
    #)




