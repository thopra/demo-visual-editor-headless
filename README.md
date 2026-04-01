# headless + visual_editor demo setup

Demo setup with forked versions of friendsoftypo3/headless and friendsoftypo3/visual_editor

## Setup

1. Clone this repository
2. run ``ddev start``
3. run ``ddev ssh``, then in the container run ``cd frontend && npm install`` and finally run ``npm run dev`` to start nuxt in dev mode

https://api.typo3.ddev.site should now show the nuxt frontend (with 3 unstyled content elements), https://api.typo3.ddev.site/api will show the headless api endpoint and the TYPO3 backend should be available at https://api.typo3.ddev.site/typo3

## Proof-of-Concept

This repository is only intended to evaluate what's currently possible and where the limitations would be. Hence, most changes to packages are just quick prototyping to work out if there are any dead ends. Proper implementation would need some more work.

## What's working (kind of, with some changes)

1. Backend module shows headless site's frontendBase
2. Information about visual editing (record information, edit urls etc.) is included in headless JSON response when backend user is authenticated
3. nuxt app renders custom elements for editing (see example components in /frontend/)

## What isn't ...

1. Reliable CORS / multi domain setup: The backend module only works in a single origin context. More on that below.
2. Saving changes or opening the editing modal. This is due to the "settings" on the window object not being available that are rendered as inline JS by the page renderer.
3. Navigating to other pages than the root page in the page tree will fail because cHash validation fails. Don't know why, yet.
4. Only works when SSR is enabled. This is because the JS modules that need to be injected to the frontend rely on an importmap that is usually created in the PageRenderer. Worked around this by providing the whole importmap as a stringified JSON in the headless initialData response and inject it during SSR. This is *not* a viable solution. 


## Example Output for headless page when in editing mode

``` 
{
  "id": 1,
  "type": "Standard",
  "slug": "/",
  
  ...
  
  "visualEditor": {
    "pageId": 1,
    "languageId": 0,
    "backendEditUrl": "/typo3/module/web/edit?token=dddbdb5df848fb8bcae70b2d2f5740c76fe8d506dbbdb9ad91b9e9b2470bfa45&id=1&languages%5B0%5D=0",
    "newContentUrl": "/typo3/record/content/wizard/new?token=d64ca4b8aaa4eaf95df6798b991366fbf62c9784e79c590c8dd7c351e82c3e65&id=1&colPos=__COL_POS__&uid_pid=__UID_PID__&returnUrl=/typo3/module/web/edit?token%3Ddddbdb5df848fb8bcae70b2d2f5740c76fe8d506dbbdb9ad91b9e9b2470bfa45%26id%3D1%26languages%255B0%255D%3D0",
    "editContentUrl": "/typo3/record/edit?token=41b20abfb4bd33222146910bb6aedca12d81934f88410d13a09349f84f9f0d7a&edit%5B__TABLE__%5D%5B__UID__%5D=edit&returnUrl=/typo3/module/web/edit?token%3Ddddbdb5df848fb8bcae70b2d2f5740c76fe8d506dbbdb9ad91b9e9b2470bfa45%26id%3D1%26languages%255B0%255D%3D0&module=web_edit",
    "editContentContextualUrl": "/typo3/record/edit/contextual?token=fb1e478f5f5b9557963fd8c7e0b2753eba08e506de3857e45b4bd430b916538d&edit%5B__TABLE__%5D%5B__UID__%5D=edit&returnUrl=/typo3/module/web/edit?token%3Ddddbdb5df848fb8bcae70b2d2f5740c76fe8d506dbbdb9ad91b9e9b2470bfa45%26id%3D1%26languages%255B0%255D%3D0&module=web_edit",
    "allowNewContent": true,
    "token": "12825aaaacf0476059e2917a0391f3834e4ebf7337cace45936802a4cbe08a65",
    "routeArguments": [],
    "allowedOrigins": [
      "https://api.typo3.ddev.site"
    ]
  },
  "content": {
    "colPos0": [
      {
        "id": 1,
        "type": "text",
        "colPos": 0,
        "categories": "",
        "appearance": {
          "layout": "default",
          "frameClass": "default",
          "spaceBefore": "",
          "spaceAfter": ""
        },
        "visualEditor": {
          "record": {
            "elementName": "Regular Text Element",
            "CType": "text",
            "table": "tt_content",
            "id": "tt_content:1",
            "uid": "1",
            "pid": "1",
            "colPos": 0,
            "hiddenFieldName": "hidden",
            "canModifyRecord": "true",
            "canBeMoved": "true"
          },
          "fields": [
            {
              "table": "tt_content",
              "name": "Page Content: Header",
              "field": "header",
              "title": "edit Page Content: Header",
              "allowNewlines": false,
              "value": "Welcome to your default website",
              "richtext": false,
              "richtextOptions": null,
              "uid": 1,
              "id": "tt_content:1"
            },
            {
              "table": "tt_content",
              "name": "Page Content: Subheader",
              "field": "subheader",
              "title": "edit Page Content: Subheader",
              "allowNewlines": false,
              "value": "",
              "richtext": false,
              "richtextOptions": null,
              "uid": 1,
              "id": "tt_content:1"
            },
            {
              "table": "tt_content",
              "name": "Page Content: Text",
              "field": "bodytext",
              "title": "edit Page Content: Text",
              "allowNewlines": true,
              "value": "\u003Cp\u003EThis website is made with \u003Ca href=\"https://typo3.org\" target=\"_blank\" rel=\"noreferrer\"\u003ETYPO3\u003C/a\u003E and headless.\u003C/p\u003E\r\n\u003Cp\u003E&nbsp;\u003C/p\u003E",
              "richtext": true,
              "richtextOptions": {
                "customConfig": "",
                "label": "Text",
                "alignment": {
                  "options": [
                    {
                      "name": "left",
                      "className": "text-start"
                    },
                    {
                      "name": "center",
                      "className": "text-center"
                    },
                    {
                      "name": "right",
                      "className": "text-end"
                    },
                    {
                      "name": "justify",
                      "className": "text-justify"
                    }
                  ]
                },
                "contentsCss": [],
                "heading": {
                  "options": [
                    {
                      "model": "paragraph",
                      "title": "Paragraph"
                    },
                    {
                      "model": "heading2",
                      "view": "h2",
                      "title": "Heading 2"
                    },
                    {
                      "model": "heading3",
                      "view": "h3",
                      "title": "Heading 3"
                    },
                    {
                      "model": "formatted",
                      "view": "pre",
                      "title": "Pre-Formatted Text"
                    }
                  ]
                },
                "importModules": [
                  {
                    "module": "@typo3/rte-ckeditor/plugin/whitespace.js",
                    "exports": [
                      "Whitespace"
                    ]
                  },
                  {
                    "module": "@typo3/rte-ckeditor/plugin/typo3-link.js",
                    "exports": [
                      "Typo3Link"
                    ]
                  }
                ],
                "style": {
                  "definitions": [
                    {
                      "name": "Lead",
                      "element": "p",
                      "classes": [
                        "lead"
                      ]
                    },
                    {
                      "name": "Small",
                      "element": "small",
                      "classes": [
                        ""
                      ]
                    },
                    {
                      "name": "Muted",
                      "element": "span",
                      "classes": [
                        "text-muted"
                      ]
                    }
                  ]
                },
                "table": {
                  "defaultHeadings": {
                    "rows": 1
                  },
                  "contentToolbar": [
                    "tableColumn",
                    "tableRow",
                    "mergeTableCells",
                    "tableProperties",
                    "tableCellProperties",
                    "toggleTableCaption"
                  ]
                },
                "toolbar": {
                  "items": [
                    "style",
                    "heading",
                    "|",
                    "bold",
                    "italic",
                    "subscript",
                    "superscript",
                    "softhyphen",
                    "|",
                    "bulletedList",
                    "numberedList",
                    "blockQuote",
                    "alignment",
                    "|",
                    "findAndReplace",
                    "link",
                    "|",
                    "removeFormat",
                    "undo",
                    "redo",
                    "|",
                    "insertTable",
                    "|",
                    "specialCharacters",
                    "horizontalLine",
                    "sourceEditing"
                  ],
                  "removeItems": [],
                  "shouldNotGroupWhenFull": true
                },
                "ui": {
                  "poweredBy": {
                    "position": "inside",
                    "side": "right",
                    "label": ""
                  }
                },
                "width": "auto",
                "wordCount": {
                  "displayCharacters": true,
                  "displayWords": true
                },
                "language": {
                  "ui": "en",
                  "content": "en-us"
                },
                "debug": false,
                "typo3link": {
                  "route": "rteckeditor_wizard_browse_links",
                  "routeUrl": "/typo3/rte/wizard/browselinks?token=9df230475fabb850e2619510b86b807155c599a64be1bd81a02748738c861048&P%5Btable%5D=tt_content&P%5Buid%5D=1&P%5BfieldName%5D=bodytext&P%5BrecordType%5D=text&P%5Bpid%5D=1&P%5BrichtextConfigurationName%5D=default"
                }
              },
              "uid": 1,
              "id": "tt_content:1"
            },
            {
              "table": "tt_content",
              "name": "Page Content: Link label",
              "field": "tx_themecamino_link_label",
              "title": "edit Page Content: Link label",
              "allowNewlines": false,
              "value": "",
              "richtext": false,
              "richtextOptions": null,
              "uid": 1,
              "id": "tt_content:1"
            }
          ]
        },
        "content": {
          "header": "Welcome to your default website",
          "subheader": "",
          "headerLayout": 0,
          "headerPosition": "",
          "headerLink": "",
          "bodytext": "\u003Cp\u003EThis website is made with \u003Ca href=\"https://typo3.org\" target=\"_blank\" rel=\"noreferrer\"\u003ETYPO3\u003C/a\u003E and headless.\u003C/p\u003E\n\u003Cp\u003E&nbsp;\u003C/p\u003E"
        }
      },
  ...
}
```

## Necessary changes

The following problems/additions need to be addressed in each package:

### visual_editor

#### 1. Provide one or more classes as public API to other extension developers

We need some public service class(es) that will provide the following data:

* Page information (currently written to inline Javascript in PageRenderer)
* Attributes of custom elements ve-editable-text, ve-editable-rich-text and ve-content-area (In this demo, I've used the ``EditModeService`` class for this and added some methods that return arrays with the required data, but it probably makes more sense to return the TagBuilder directly)
* a list of resources (JS and CSS) that need to be loaded from the assets directory to initialize editing

#### 2. Not required, but would be nice: Event / Callback when changes require a reload

It would be nice to have an event being dispatched that we can use to reload the pagedata from the api or react to changes to tha page that require to update state in the frontend

#### 3. Proper CORS support or documentation of the limitations

This demo uses a single domain setup (see nginx config). Cross domain setup also works up to a certain point, but due to limitations in the TYPO3 core, messaging between frames in the backend module will never work, unless major changes are coming to TYPO3 itself.

While we CAN do this (by reverse-proxying anything other than /typo3 /_assets and /fileadmin to nuxt) this also means that effectively this excludes multi-domain setups in general. I have not found a way around this so far.

### headless

#### 1. Add DataProcessors for editing information

Once visual-editor provides classes to get the editing information for pages and records, headless should optionally include TS configuration and some DataProcessors to add this data to the JSON page and initialData

See:

packages/headless/Classes/DataProcessing/VisualEditingPageInformationProcessor.php
packages/headless/Classes/DataProcessing/VisualEditingRecordInformationProcessor.php

#### 2. Add a way to provide JavaScript (and CSS) that is added by the PageRenderer to the frontend

This is a big one: All of this can only work, if the JS resources that are loaded from the extension visual_editor are provided to the frontend. Currently, headless has no concept of this because it would usually not be coupled to JS modules from extensions.

While we could just generate a URL to the public assets of a JS file, the problem is that the ES Modules rely on an importmap that is usually rendered in the head of the page by the PageRenderer. 

There is also some inline javascript rendered by the core that adds required properties to the window object, like "settings", if a backend user is authenticated.

Without it, we cannot load any JS modules that have been added via the JavaScriptRenderer und thus this approach will not work.

To work around this, in this demo I have just injected the importmap aswell, but that only works when ssr is enabled. We can't dynamically add an importmap on the client side.

See:

packages/headless/Classes/DataProcessing/VisualEditingResourcesInformationProcessor.php

### nuxt-typo3

#### 1. Pass cookies to api during SSR

Not sure if this is related to visual editing or a CORS problem in general. Faced an issue where cookies where not included in the api request during SSR.

The editing information should only be included for authenticated backend users, so without an established session it will not be rendered.

I was able to fix this by replacing ofetch with nuxt's useRequestFetch(), see frontend/patches but that would need some work.

#### 2. Add components that wrap visual editing web components

I have added some examples in frontend/components for content areas and individual fields.

In general, it would be better to include the tag name in the API response also, so that the tag is build dynamically by what visueal_editor provides as data.
That way, it would be more future prove because visual_editor would have full control of what's being rendered. Not sure if this will work out though.