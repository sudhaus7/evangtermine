<?php

namespace ArbkomEKvW\Evangtermine\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Event
 */
class Event extends AbstractEntity
{
    protected string $id = '';
    protected ?DateTime $start = null;
    protected ?DateTime $end = null;
    protected string $mode = '';
    protected string $subtitle = '';
    protected string $datum1 = '';
    protected string $datum2 = '';
    protected string $monthbar = '';
    protected bool $allday = true;
    protected int $eventId = 0;
    protected int $eventInputmaskId = 0;
    protected string $title = '';
    protected string $categories = '';
    protected string $people = '';
    protected string $shortDescription = '';
    protected string $longDescription = '';
    protected string $link = '';
    protected string $eventKat = '';
    protected string $eventKat2 = '';
    protected string $email = '';
    protected int $eventPersonId = 0;
    protected int $eventPlaceId = 0;
    protected string $region = '';
    protected string $eventSubregionId = '';
    protected string $eventRegion2Id = '';
    protected string $eventRegion3Id = '';
    protected string $eventProfessionId = '';
    protected string $eventMusicKatId = '';
    protected string $eventFlag1 = '';
    protected string $textline1 = '';
    protected string $textline2 = '';
    protected string $textline3 = '';
    protected string $textline4 = '';
    protected string $textline5 = '';
    protected string $textline6 = '';
    protected string $textline7 = '';
    protected string $textline8 = '';
    protected string $textbox1 = '';
    protected string $textbox2 = '';
    protected string $textbox3 = '';
    protected string $eventNumber1 = '';
    protected string $eventNumber2 = '';
    protected string $eventNumber3 = '';
    protected string $eventMenue1 = '';
    protected string $eventMenue2 = '';
    protected string $eventYesno1 = '';
    protected string $eventYesno2 = '';
    protected string $eventYesno3 = '';
    protected string $eventDestination = '';
    protected string $eventStatus = '';
    protected string $feedbackId = '';
    protected int $highlight = 0;
    protected string $eventCoursetype = '';
    protected string $eventCare = '';
    protected string $eventKollekte = '';
    protected string $eventStatistik = '';
    protected int $eventExternalId = 0;
    protected string $eventAccess = '';
    protected string $eventLang = '';
    protected int $eventUserId = 0;
    protected string $image = '';
    protected string $caption = '';
    protected string $eventModified = '';
    protected string $eventKollDescr = '';
    protected string $pollId = '';
    protected string $webformLinkname = '';
    protected string $inputmaskName = '';
    protected string $placeId = '';
    protected string $placeName = '';
    protected string $placeStreetNr = '';
    protected string $placeZip = '';
    protected string $placeCity = '';
    protected string $placeInfo = '';
    protected string $placeHidden = '';
    protected string $placeImage = '';
    protected string $placeImageCaption = '';
    protected int $placePosition = 0;
    protected string $placeKat = '';
    protected string $placeOpen = '';
    protected string $placeEquip = '';
    protected string $placeEquiptext = '';
    protected string $placeRegion = '';
    protected string $lat = '';
    protected string $lon = '';
    protected string $personName = '';
    protected string $personEmail = '';
    protected string $personContact = '';
    protected int $personPosition = 0;
    protected string $personSurname = '';
    protected int $userId = 0;
    protected string $userRealname = '';
    protected string $userDescription = '';
    protected string $userStreetNr = '';
    protected string $userZip = '';
    protected string $userCity = '';
    protected string $userEmail = '';
    protected string $userUrl = '';
    protected string $userContact = '';
    protected string $userIntdata = '';
    protected string $userImage = '';
    protected string $liturgBez = '';
    protected string $channels = '';
    protected string $attributes = '';
    protected string $slug = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getStart(): ?DateTime
    {
        return $this->start;
    }

    public function setStart(?DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    public function setEnd(?DateTime $end): void
    {
        $this->end = $end;
    }

    public function getDatum1(): string
    {
        return $this->datum1;
    }

    public function setDatum1(string $datum1): void
    {
        $this->datum1 = $datum1;
    }

    public function getDatum2(): string
    {
        return $this->datum2;
    }

    public function setDatum2(string $datum2): void
    {
        $this->datum2 = $datum2;
    }

    public function getMonthbar(): string
    {
        return $this->monthbar;
    }

    public function setMonthbar(string $monthbar): void
    {
        $this->monthbar = $monthbar;
    }

    public function getLiturgBez(): string
    {
        return $this->liturgBez;
    }

    public function setLiturgBez(string $liturgBez): void
    {
        $this->liturgBez = $liturgBez;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getLongDescription(): string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): void
    {
        $this->longDescription = $longDescription;
    }

    public function getPlaceId(): string
    {
        return $this->placeId;
    }

    public function setPlaceId(string $placeId): void
    {
        $this->placeId = $placeId;
    }

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    public function setPlaceName(string $placeName): void
    {
        $this->placeName = $placeName;
    }

    public function getPlaceStreetNr(): string
    {
        return $this->placeStreetNr;
    }

    public function setPlaceStreetNr(string $placeStreetNr): void
    {
        $this->placeStreetNr = $placeStreetNr;
    }

    public function getPlaceZip(): string
    {
        return $this->placeZip;
    }

    public function setPlaceZip(string $placeZip): void
    {
        $this->placeZip = $placeZip;
    }

    public function getPlaceCity(): string
    {
        return $this->placeCity;
    }

    public function setPlaceCity(string $placeCity): void
    {
        $this->placeCity = $placeCity;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    public function getHighlight(): int
    {
        return $this->highlight;
    }

    public function setHighlight(int $highlight): void
    {
        $this->highlight = $highlight;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getLat(): string
    {
        return $this->lat;
    }

    public function setLat(string $lat): void
    {
        $this->lat = $lat;
    }

    public function getLon(): string
    {
        return $this->lon;
    }

    public function setLon(string $lon): void
    {
        $this->lon = $lon;
    }

    public function getPeople(): string
    {
        return $this->people;
    }

    public function setPeople(string $people): void
    {
        $this->people = $people;
    }

    public function getCategories(): string
    {
        return $this->categories;
    }

    public function setCategories(string $categories): void
    {
        $this->categories = $categories;
    }

    public function getPollId(): string
    {
        return $this->pollId;
    }

    public function setPollId(string $pollId): void
    {
        $this->pollId = $pollId;
    }

    public function getWebformLinkname(): string
    {
        return $this->webformLinkname;
    }

    public function setWebformLinkname(string $webformLinkname): void
    {
        $this->webformLinkname = $webformLinkname;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): void
    {
        $this->caption = $caption;
    }

    public function getTextbox1(): string
    {
        return $this->textbox1;
    }

    public function setTextbox1(string $textbox1): void
    {
        $this->textbox1 = $textbox1;
    }

    public function getTextbox2(): string
    {
        return $this->textbox2;
    }

    public function setTextbox2(string $textbox2): void
    {
        $this->textbox2 = $textbox2;
    }

    public function getTextbox3(): string
    {
        return $this->textbox3;
    }

    public function setTextbox3(string $textbox3): void
    {
        $this->textbox3 = $textbox3;
    }

    public function getTextline1(): string
    {
        return $this->textline1;
    }

    public function setTextline1(string $textline1): void
    {
        $this->textline1 = $textline1;
    }

    public function getTextline2(): string
    {
        return $this->textline2;
    }

    public function setTextline2(string $textline2): void
    {
        $this->textline2 = $textline2;
    }

    public function getTextline3(): string
    {
        return $this->textline3;
    }

    public function setTextline3(string $textline3): void
    {
        $this->textline3 = $textline3;
    }

    public function getTextline4(): string
    {
        return $this->textline4;
    }

    public function setTextline4(string $textline4): void
    {
        $this->textline4 = $textline4;
    }

    public function getTextline5(): string
    {
        return $this->textline5;
    }

    public function setTextline5(string $textline5): void
    {
        $this->textline5 = $textline5;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function getFeedbackId(): string
    {
        return $this->feedbackId;
    }

    public function setFeedbackId(string $feedbackId): void
    {
        $this->feedbackId = $feedbackId;
    }

    public function getPlaceInfo(): string
    {
        return $this->placeInfo;
    }

    public function setPlaceInfo(string $placeInfo): void
    {
        $this->placeInfo = $placeInfo;
    }

    public function getPlaceImage(): string
    {
        return $this->placeImage;
    }

    public function setPlaceImage(string $placeImage): void
    {
        $this->placeImage = $placeImage;
    }

    public function getPlaceImageCaption(): string
    {
        return $this->placeImageCaption;
    }

    public function setPlaceImageCaption(string $placeImageCaption): void
    {
        $this->placeImageCaption = $placeImageCaption;
    }

    public function getPersonName(): string
    {
        return $this->personName;
    }

    public function setPersonName(string $personName): void
    {
        $this->personName = $personName;
    }

    public function getPersonEmail(): string
    {
        return $this->personEmail;
    }

    public function setPersonEmail(string $personEmail): void
    {
        $this->personEmail = $personEmail;
    }

    public function getPersonContact(): string
    {
        return $this->personContact;
    }

    public function setPersonContact(string $personContact): void
    {
        $this->personContact = $personContact;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserRealname(): string
    {
        return $this->userRealname;
    }

    public function setUserRealname(string $userRealname): void
    {
        $this->userRealname = $userRealname;
    }

    public function getUserStreetNr(): string
    {
        return $this->userStreetNr;
    }

    public function setUserStreetNr(string $userStreetNr): void
    {
        $this->userStreetNr = $userStreetNr;
    }

    public function getUserZip(): string
    {
        return $this->userZip;
    }

    public function setUserZip(string $userZip): void
    {
        $this->userZip = $userZip;
    }

    public function getUserCity(): string
    {
        return $this->userCity;
    }

    public function setUserCity(string $userCity): void
    {
        $this->userCity = $userCity;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): void
    {
        $this->userEmail = $userEmail;
    }

    public function getUserUrl(): string
    {
        return $this->userUrl;
    }

    public function setUserUrl(string $userUrl): void
    {
        $this->userUrl = $userUrl;
    }

    public function getUserContact(): string
    {
        return $this->userContact;
    }

    public function setUserContact(string $userContact): void
    {
        $this->userContact = $userContact;
    }

    public function getUserDescription(): string
    {
        return $this->userDescription;
    }

    public function setUserDescription(string $userDescription): void
    {
        $this->userDescription = $userDescription;
    }

    public function getUserImage(): string
    {
        return $this->userImage;
    }

    public function setUserImage(string $userImage): void
    {
        $this->userImage = $userImage;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function isAllday(): bool
    {
        return $this->allday;
    }

    public function setAllday(bool $allday): void
    {
        $this->allday = $allday;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getEventInputmaskId(): int
    {
        return $this->eventInputmaskId;
    }

    public function setEventInputmaskId(int $eventInputmaskId): void
    {
        $this->eventInputmaskId = $eventInputmaskId;
    }

    public function getEventKat(): string
    {
        return $this->eventKat;
    }

    public function setEventKat(string $eventKat): void
    {
        $this->eventKat = $eventKat;
    }

    public function getEventKat2(): string
    {
        return $this->eventKat2;
    }

    public function setEventKat2(string $eventKat2): void
    {
        $this->eventKat2 = $eventKat2;
    }

    public function getEventPersonId(): int
    {
        return $this->eventPersonId;
    }

    public function setEventPersonId(int $eventPersonId): void
    {
        $this->eventPersonId = $eventPersonId;
    }

    public function getEventPlaceId(): int
    {
        return $this->eventPlaceId;
    }

    public function setEventPlaceId(int $eventPlaceId): void
    {
        $this->eventPlaceId = $eventPlaceId;
    }

    public function getEventSubregionId(): string
    {
        return $this->eventSubregionId;
    }

    public function setEventSubregionId(string $eventSubregionId): void
    {
        $this->eventSubregionId = $eventSubregionId;
    }

    public function getEventRegion2Id(): string
    {
        return $this->eventRegion2Id;
    }

    public function setEventRegion2Id(string $eventRegion2Id): void
    {
        $this->eventRegion2Id = $eventRegion2Id;
    }

    public function getEventRegion3Id(): string
    {
        return $this->eventRegion3Id;
    }

    public function setEventRegion3Id(string $eventRegion3Id): void
    {
        $this->eventRegion3Id = $eventRegion3Id;
    }

    public function getEventProfessionId(): string
    {
        return $this->eventProfessionId;
    }

    public function setEventProfessionId(string $eventProfessionId): void
    {
        $this->eventProfessionId = $eventProfessionId;
    }

    public function getEventMusicKatId(): string
    {
        return $this->eventMusicKatId;
    }

    public function setEventMusicKatId(string $eventMusicKatId): void
    {
        $this->eventMusicKatId = $eventMusicKatId;
    }

    public function getEventFlag1(): string
    {
        return $this->eventFlag1;
    }

    public function setEventFlag1(string $eventFlag1): void
    {
        $this->eventFlag1 = $eventFlag1;
    }

    public function getTextline6(): string
    {
        return $this->textline6;
    }

    public function setTextline6(string $textline6): void
    {
        $this->textline6 = $textline6;
    }

    public function getTextline7(): string
    {
        return $this->textline7;
    }

    public function setTextline7(string $textline7): void
    {
        $this->textline7 = $textline7;
    }

    public function getTextline8(): string
    {
        return $this->textline8;
    }

    public function setTextline8(string $textline8): void
    {
        $this->textline8 = $textline8;
    }

    public function getEventNumber1(): string
    {
        return $this->eventNumber1;
    }

    public function setEventNumber1(string $eventNumber1): void
    {
        $this->eventNumber1 = $eventNumber1;
    }

    public function getEventNumber2(): string
    {
        return $this->eventNumber2;
    }

    public function setEventNumber2(string $eventNumber2): void
    {
        $this->eventNumber2 = $eventNumber2;
    }

    public function getEventNumber3(): string
    {
        return $this->eventNumber3;
    }

    public function setEventNumber3(string $eventNumber3): void
    {
        $this->eventNumber3 = $eventNumber3;
    }

    public function getEventMenue1(): string
    {
        return $this->eventMenue1;
    }

    public function setEventMenue1(string $eventMenue1): void
    {
        $this->eventMenue1 = $eventMenue1;
    }

    public function getEventMenue2(): string
    {
        return $this->eventMenue2;
    }

    public function setEventMenue2(string $eventMenue2): void
    {
        $this->eventMenue2 = $eventMenue2;
    }

    public function getEventYesno1(): string
    {
        return $this->eventYesno1;
    }

    public function setEventYesno1(string $eventYesno1): void
    {
        $this->eventYesno1 = $eventYesno1;
    }

    public function getEventYesno2(): string
    {
        return $this->eventYesno2;
    }

    public function setEventYesno2(string $eventYesno2): void
    {
        $this->eventYesno2 = $eventYesno2;
    }

    public function getEventYesno3(): string
    {
        return $this->eventYesno3;
    }

    public function setEventYesno3(string $eventYesno3): void
    {
        $this->eventYesno3 = $eventYesno3;
    }

    public function getEventDestination(): string
    {
        return $this->eventDestination;
    }

    public function setEventDestination(string $eventDestination): void
    {
        $this->eventDestination = $eventDestination;
    }

    public function getEventStatus(): string
    {
        return $this->eventStatus;
    }

    public function setEventStatus(string $eventStatus): void
    {
        $this->eventStatus = $eventStatus;
    }

    public function getEventCoursetype(): string
    {
        return $this->eventCoursetype;
    }

    public function setEventCoursetype(string $eventCoursetype): void
    {
        $this->eventCoursetype = $eventCoursetype;
    }

    public function getEventCare(): string
    {
        return $this->eventCare;
    }

    public function setEventCare(string $eventCare): void
    {
        $this->eventCare = $eventCare;
    }

    public function getEventKollekte(): string
    {
        return $this->eventKollekte;
    }

    public function setEventKollekte(string $eventKollekte): void
    {
        $this->eventKollekte = $eventKollekte;
    }

    public function getEventStatistik(): string
    {
        return $this->eventStatistik;
    }

    public function setEventStatistik(string $eventStatistik): void
    {
        $this->eventStatistik = $eventStatistik;
    }

    public function getEventExternalId(): int
    {
        return $this->eventExternalId;
    }

    public function setEventExternalId(int $eventExternalId): void
    {
        $this->eventExternalId = $eventExternalId;
    }

    public function getEventAccess(): string
    {
        return $this->eventAccess;
    }

    public function setEventAccess(string $eventAccess): void
    {
        $this->eventAccess = $eventAccess;
    }

    public function getEventLang(): string
    {
        return $this->eventLang;
    }

    public function setEventLang(string $eventLang): void
    {
        $this->eventLang = $eventLang;
    }

    public function getEventUserId(): int
    {
        return $this->eventUserId;
    }

    public function setEventUserId(int $eventUserId): void
    {
        $this->eventUserId = $eventUserId;
    }

    public function getEventModified(): string
    {
        return $this->eventModified;
    }

    public function setEventModified(string $eventModified): void
    {
        $this->eventModified = $eventModified;
    }

    public function getEventKollDescr(): string
    {
        return $this->eventKollDescr;
    }

    public function setEventKollDescr(string $eventKollDescr): void
    {
        $this->eventKollDescr = $eventKollDescr;
    }

    public function getInputmaskName(): string
    {
        return $this->inputmaskName;
    }

    public function setInputmaskName(string $inputmaskName): void
    {
        $this->inputmaskName = $inputmaskName;
    }

    public function getPlaceHidden(): string
    {
        return $this->placeHidden;
    }

    public function setPlaceHidden(string $placeHidden): void
    {
        $this->placeHidden = $placeHidden;
    }

    public function getPlacePosition(): int
    {
        return $this->placePosition;
    }

    public function setPlacePosition(int $placePosition): void
    {
        $this->placePosition = $placePosition;
    }

    public function getPlaceKat(): string
    {
        return $this->placeKat;
    }

    public function setPlaceKat(string $placeKat): void
    {
        $this->placeKat = $placeKat;
    }

    public function getPlaceOpen(): string
    {
        return $this->placeOpen;
    }

    public function setPlaceOpen(string $placeOpen): void
    {
        $this->placeOpen = $placeOpen;
    }

    public function getPlaceEquip(): string
    {
        return $this->placeEquip;
    }

    public function setPlaceEquip(string $placeEquip): void
    {
        $this->placeEquip = $placeEquip;
    }

    public function getPlaceEquiptext(): string
    {
        return $this->placeEquiptext;
    }

    public function setPlaceEquiptext(string $placeEquiptext): void
    {
        $this->placeEquiptext = $placeEquiptext;
    }

    public function getPlaceRegion(): string
    {
        return $this->placeRegion;
    }

    public function setPlaceRegion(string $placeRegion): void
    {
        $this->placeRegion = $placeRegion;
    }

    public function getPersonPosition(): int
    {
        return $this->personPosition;
    }

    public function setPersonPosition(int $personPosition): void
    {
        $this->personPosition = $personPosition;
    }

    public function getPersonSurname(): string
    {
        return $this->personSurname;
    }

    public function setPersonSurname(string $personSurname): void
    {
        $this->personSurname = $personSurname;
    }

    public function getUserIntdata(): string
    {
        return $this->userIntdata;
    }

    public function setUserIntdata(string $userIntdata): void
    {
        $this->userIntdata = $userIntdata;
    }

    public function getChannels(): string
    {
        return $this->channels;
    }

    public function setChannels(string $channels): void
    {
        $this->channels = $channels;
    }

    public function getAttributes(): string
    {
        return $this->attributes;
    }

    public function getAttributesAsArray(): array
    {
        if (empty($this->attributes)) {
            return [];
        }
        $attributesArray = json_decode($this->attributes, true);
        foreach ($attributesArray as $key => $value) {
            if (!empty($value['db'])) {
                $attributesArray[$key]['db'] = explode(',', $value['db']);
            }
        }
        return $attributesArray;
    }

    public function setAttributes(string $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
