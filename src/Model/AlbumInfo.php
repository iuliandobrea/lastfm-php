<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\LastFm\Model;

/**
 * @SuppressWarnings("PHPMD.ExcessiveParameterList")
 */
final class AlbumInfo
{
    private ?string $name;

    private ?Artist $artist;

    private ?string $mbid;

    private ?string $url;

    /**
     * @var Image[]
     */
    private array $images;

    private int $listeners;

    private int $playcount;

    /**
     * @var Song[]
     */
    private array $tracks;

    /**
     * @var Tag[]
     */
    private array $tags;

    private ?string $wikiSummary;

    /**
     * @param Image[] $images
     * @param Song[]  $tracks
     * @param Tag[]   $tags
     */
    public function __construct(
        ?string $name,
        ?Artist $artist,
        ?string $mbid,
        ?string $url,
        array $images,
        int $listeners,
        int $playcount,
        array $tracks,
        array $tags,
        ?string $wikiSummary
    ) {
        $this->name         = $name;
        $this->artist       = $artist;
        $this->mbid         = $mbid;
        $this->url          = $url;
        $this->images       = $images;
        $this->listeners    = $listeners;
        $this->playcount    = $playcount;
        $this->tracks       = $tracks;
        $this->tags         = $tags;
        $this->wikiSummary  = $wikiSummary;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function getMbid(): ?string
    {
        return $this->mbid;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return Image[]
     */
    public function getImage(): array
    {
        return $this->images;
    }

    public function getListeners(): int
    {
        return $this->listeners;
    }

    public function getPlaycount(): int
    {
        return $this->playcount;
    }

    /**
     * @return Song[]
     */
    public function getTracks(): array
    {
        return $this->tracks;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getWikiSummary(): ?string
    {
        return $this->wikiSummary;
    }

    public static function fromApi(array $data): self
    {
        $images = self::createImagesFromApi($data);
        $tracks = self::createTracksFromApi($data);
        $tags   = self::createTagsFromApi($data);

        return new self(
            $data['name'],
            new Artist($data['artist'], null, [], null),
            $data['mbid'] ?? null,
            $data['url']  ?? null,
            $images,
            (int)($data['listeners'] ?? 0),
            (int)($data['playcount'] ?? 0),
            $tracks,
            $tags,
            $data['wiki']['summary'] ?? null
        );
    }

    private static function createImagesFromApi(array $data): array
    {
        $images = [];

        if (isset($data['image']) && \is_array($data['image'])) {
            foreach ($data['image'] as $image) {
                $images[] = new Image($image['#text']);
            }
        }

        return $images;
    }

    private static function createTracksFromApi(array $data): array
    {
        $tracks = [];

        // data['tracks']['track'] can be:
        // array of multiple track elements > Example: https://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=API_KEY&mbid=87f72085-bdd9-4eba-acbd-3f92c4c98aa9&format=json
        // single track element > Example: https://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=API_KEY&mbid=51ca0481-d5b6-49e2-a5db-e9b920ebc074&format=json
        if (isset($data['tracks']['track'][0])) {
            foreach ($data['tracks']['track'] as $track) {
                if (!\is_array($track)) {
                    continue;
                }

                $tracks[] = Song::fromApi($track);
            }
        } elseif (!empty($data['tracks']['track'])) {
            $tracks[] = Song::fromApi($data['tracks']['track']);
        }

        return $tracks;
    }

    private static function createTagsFromApi(array $data): array
    {
        $tags = [];

        if (isset($data['tags']['tag']) && \is_array($data['tags']['tag'])) {
            foreach ($data['tags']['tag'] as $tag) {
                if (!\is_array($tag)) {
                    continue;
                }

                $tags[] = Tag::fromApi($tag);
            }
        }

        return $tags;
    }
}
