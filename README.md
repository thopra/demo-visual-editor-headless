# headless + visual_editor demo setup

Demo setup with forked versions of friendsoftypo3/headless and friendsoftypo3/visual_editor

## Setup

1. Clone this repository
2. run ``ddev start``
3. run ``ddev ssh``, then in the container run ``cd frontend && npm install`` and finally run ``npm run dev`` to start nuxt in dev mode

https://api.typo3.ddev.site should now show the nuxt frontend (with 3 unstyled content elements), https://api.typo3.ddev.site/api will show the headless api endpoint and the TYPO3 backend should be available at https://api.typo3.ddev.site/typo3

## Proof-of-Concept

This repository is only intended to evaluate what's currently possible and where the limitations would be. Hence, most changes to packages are just quick fixes to get to the next problem until hitting a wall that cannot be overcome without changes in the TYPO3 core and will need a lot more work.

## What's working (kind of, with some changes)

1. Backend module shows headless site's frontendBase
2. Information about visual editing (record information, edit urls etc.) are included in headless JSON response when backend user is authenticated
3. nuxt app renders custom elements for editing (see example components in /frontend/)

## What isn't ...

1. Reliable CORS / multi domain setup: The backend module only works in a single origin context. More on that below.
2. Saving changes or opening the editing modal. This is due to the "settings" on the window object not being available that are rendered as inline JS by the page renderer.
3. Navigating to other pages than the root page in the page tree will fail because cHash validation fails. Don't know why, yet.
4. Only works when SSR is enabled. This is because the JS modules that need to be injected to the frontend rely on an importmap that is usually created in the PageRenderer. Worked around this by providing the whole importmap as a stringified JSON in the headless initialData response and inject it during SSR. This is *not* a viable solution. 

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

Once visual-editor provides classes to get the editing information for pages and records, headless should optionally include TS configuration and some DataPRocessors to add this data to the JSON page and initialData

See:

packages/headless/Classes/DataProcessing/VisualEditingPageInformationProcessor.php
packages/headless/Classes/DataProcessing/VisualEditingRecordInformationProcessor.php

#### 2. Add a way to provide JavaScript (and CSS) that is added by the PageRenderer to the frontend

This is a big one: All of this can only work, if the JS resources that are loaded from the extension visual_editor are provided to the frontend. Currently, headless has no concept of this because it would usually not be coupled to JS modules from extensions.

While we could just generate a URL to the public assets of a JS file, the problem is that the ES Modules rely on an importmap that is usually rendered in the head of the page by the PageRenderer. Without it, we cannot load any JS modules that have been added via the JavaScriptRenderer und thus this approach will not work.

To work around this, in this demo I have just injected the importmap aswell, but that only works when ssr is enabled. We can't dynamically add an importmap on the client side.

See:

packages/headless/Classes/DataProcessing/VisualEditingResourcesInformationProcessor.php

### nuxt-typo3

#### 1. Pass cookies to api during SSR

Not sure if this is related to visual editing or a CORS problem in general. Faced an issue that cookies where not included in the api request during SSR.

The editing information should only be included for authenticated backend users, so without an established session it will not be rendered.

I was able to fix this by replacing ofetch with nuxt's useRequestFetch(), see frontend/patches but that would need some work.

#### 2. Add components that wrap visual editing web components

I have added some examples in frontend/components for content areas and individual fields.

In general, it would be better to include the tag name in the API response also, so that the tag is build dynamically by what visueal_editor provides as data.
That way, it would be more future prove because visual_editor would have full control of what's being rendered. Not sure if this will work out though.