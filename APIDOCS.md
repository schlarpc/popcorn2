# Popcorn 2 API Documentation

## Object Types

Many API calls in Popcorn 2 return common objects across the API. Here are the objects and their
potential properties. Many properties are optional and may not be included in the response object.
Required properties for each object will be marked as (required).

**Item**

An `Item` is the abstract object type that several other objects are composed from.

```
type : string (required)
name : string (required)
href : url (required)
description : string
images : array
    poster : url
    thumbnail : url
    banner : url
```

`type` may be any of `video`, `directory`, or `addon`.

`href` is a url indicating the location where full `Item` details may be retrieved.

**Directory**

`Directory` inherits from `Item` and represents a listing of additional `Item` resources. These
items should be further resolved using their `href` properties.

```
resources : array (required)
    item(s)
```

**Video**

`Video` inherits from `Item` and represents a video which can be streamed using Popcorn.

```
path : string (required)
duration : int
```

**Addon**

`Addon` inherits from `Item` and represents a video providing addon. This object exposes the
potential endpoints for the given addon.

```
category : string (required)
videos : url
search : url
download : url
```

`category` may be any of `stream` or `download`.

`videos` is a url which provides a `Directory` to browse videos provided by the `Addon`.

`search` is a url which accepts a `q` GET parameter and returns a `Directory` of results.

If `category` is `download`, any `Video.path` properties from that add-on should be sent to the
specified `Addon.download` url using a POST request. A client should not attempt to use a
`Video.path` from an add-on of this type to stream directly.


## API Reference

### Videos

`GET /api/videos`

Returns `Directory`.

***

`GET /api/videos/<hash>`

Returns `Video`.

***

`DELETE /api/videos/<hash>`

`HTTP 404` on invalid hash.

`HTTP 204` on successful delete.

***

`GET /api/videos/<hash>/thumbnail?time=<?time>`

`time` is an optional parameter, defaults to 0.

`HTTP 400` if parameters do not result in a working thumbnail.

Returns JPEG image of video at given time offset.

### Stream

`GET /api/stream`

Returns object containing current stream status.

```
streaming : bool
path : string
elapsed : int
duration : int
```

***

`POST /api/stream/play`

Data: `path=<path>&time=<?time>`

`time` is an optional parameter, defaults to 0.

`HTTP 409` if playback cannot be started.

`HTTP 204` if playback has started.

***

`POST /api/stream/stop`

`HTTP 409` if playback cannot be stopped.

`HTTP 204` if playback has stopped.

***

`POST /api/stream/pause`

`HTTP 409` if playback cannot be paused.

`HTTP 204` if playback has paused.

***

`POST /api/stream/resume`

`HTTP 409` if playback cannot be resumed.

`HTTP 204` if playback has resumed.

### Addons

`GET /api/addons`

Returns `Directory`.

***

`GET /api/addons/<addon>`

`addon` is an addon from `/api/addons`.

Returns `Addon`.

***

`GET /api/addons/<addon>/search?q=<query>`

`query` is a search query.

Returns `Directory`.

***

`GET /api/addons/<addon>/videos`

Returns `Directory`.

***

`POST /api/addons/<addon>/download`

Data: `path=<path>`

`path` is from a `Video.path` belonging to this addon.

`HTTP 400` if download cannot be started.

`HTTP 204` if download started.