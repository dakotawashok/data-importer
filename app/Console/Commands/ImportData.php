<?php

namespace App\Console\Commands;

use App\Category;
use App\Category_Translation;
use App\Collection;
use App\Collection_Translation;
use App\Destination;
use App\Mark;
use App\Mark_Translation;
use App\Media;
use App\Most_User;
use App\Offer;
use App\Offer_Collection;
use App\Offer_Destination;
use App\Offer_Location;
use App\Offer_Mark;
use App\offer_media;
use App\Offer_Translation;
use App\OldOffer;
use App\User;
use App\user_to_app_to_role;
use DateTime;
use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataimport:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from all json files in storage and put them on the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    // Variables
    protected $offers;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * Grab an array of all the file names in the json folder, then iterates through that array one at a time, gets the file
     * contents associated with that file name, and parses that json information into its respective tables.
     *
     * @return mixed
     */
    public function handle()
    {
        $folderUrl = 'app/Console/Commands/json';
        $fileNames = scandir($folderUrl);
        $mediaUrl = 'app/Console/Commands/uploads';
        $mediaNames = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($mediaUrl), \RecursiveIteratorIterator::SELF_FIRST);

        $this->info('Importing Media files...this may take a while...');
        $this->parseImageMedia($mediaNames);
        $this->info('Done Importing Media Files!');

        $bar = $this->output->createProgressBar(count($fileNames));

        foreach ($fileNames as $item) {
            if ($item != "." && $item != ".." && $item != '.DS_Store') {
                $offerContents = file_get_contents($folderUrl . '/' . $item);
                $offer = json_decode($offerContents);

                if (is_array($offer) || is_object($offer)) {
                    $this->storeOldOffer($offer);
                } else {
                    $this->error('Error!');
                }
                //print_r($offer);
            }
            $bar->advance();
        }

        $bar->finish();

        $this->setDestinationAddresses();
        $this->parseUserTable();

    }

    /**
     * Take all the file urls and get the information to put them in the media database
     *
     * @return mixed
     */
    public function parseImageMedia($objects) {
        foreach ($objects as $name => $object) {
            if (filetype($name) != 'dir') {
                $pieces = explode('app/Console/Commands/', $name);

                $now = new DateTime('NOW');

                $model = new Media();
                $model->name = basename($name);
                $model->tags = basename('');
                $model->file_name = basename($name);
                $model->file_source = basename($name);
                $model->file_path = $pieces[1];
                $model->content_type = getimagesize($name)['mime'];
                $model->width = getimagesize($name)[0];
                $model->height = getimagesize($name)[1];
                $model->content_length = filesize($name);
                $model->created_at = $now->format('Y-m-d H:i:s');
                $model->save();
            }
        }
    }

    /**
     * Take the information in the old offer model and parse it into the models for a collection entry or a destination entry,
     *  depending on what kind of entry it is.
     *
     * @return mixed
     */
    public function parseOfferCollectionTable($oldOffer) {
        $destination_json = json_decode($oldOffer->offer_destination);

        foreach($destination_json as $destination) {
            $firstChar = substr($destination->name, 0, 1);
            // if it's a collection
            if ($firstChar == '-') {
                try {
                    $testCollection = Collection_Translation::where('name', $destination->name)->where('slug', $destination->slug)->firstOrFail();

                    $offer_collection_entry = new Offer_Collection();
                    $offer_collection_entry->collection_id = $testCollection->id;
                    $offer_collection_entry->offer_id = $oldOffer->coupon_id;
                    $offer_collection_entry->save();
                } catch (ModelNotFoundException $e) {
                    $offer_collection_entry = new Offer_Collection();
                    $collection_entry = new Collection();
                    $collection_translation_entry = new Collection_Translation();

                    $collection_entry->active = true;
                    $collection_entry->save();

                    $collection_translation_entry->collection_id = $collection_entry->id;
                    $collection_translation_entry->lang = 'en';
                    $collection_translation_entry->name = $destination->name;
                    $collection_translation_entry->slug = $destination->slug;
                    $collection_translation_entry->save();

                    $offer_collection_entry->collection_id = $collection_entry->id;
                    $offer_collection_entry->offer_id = $oldOffer->coupon_id;
                    $offer_collection_entry->save();
                }
            // if it's a destination
            } else if ($firstChar != null) {
                try {
                    $testDestination = Destination::where('name', $destination->name)->where('slug', $destination->slug)->firstOrFail();

                    $offer_destination_entry = new Offer_Destination();
                    $offer_destination_entry->destination_id = $testDestination->id;
                    $offer_destination_entry->offer_id = $oldOffer->coupon_id;
                    $offer_destination_entry->save();
                } catch (ModelNotFoundException $e) {
                    $destination_entry = new Destination();
                    $offer_destination_entry = new Offer_Destination();

                    $destination_entry->name = $destination->name;
                    $destination_entry->slug = $destination->slug;
                    $destination_entry->address = $destination->name;
                    $destination_entry->id = $destination->term_id;
                    try {
                        $destination_entry->parent_id = $destination->parent;
                        $destination_entry->address = $destination->name;
                    } catch(ErrorException $e) {
                        $destination_entry->address = $destination->name;
                    }
                    $destination_entry->save();

                    $offer_destination_entry->destination_id = $destination_entry->id;
                    $offer_destination_entry->offer_id = $oldOffer->coupon_id;
                    $offer_destination_entry->save();
                }
            } else {
                $this->error('error its null');
            }
        }
    }

    public function setDestinationAddresses() {
        // set addresses from parent ID's now that they're all assigned
        $destinations = Destination::all();

        foreach($destinations as $destination) {
            if ($destination->parent_id != null) {
                try {
                    $tempParentModel = Destination::where('id', $destination->parent_id)->firstOrFail();
                    $destination->address = $destination->name . ", " . $tempParentModel->name;
                    $destination->save();
                } catch (ModelNotFoundException $e) {
                    $this->error('wtf');
                }
            }
        }
    }

    /**
     * Take the information in the old offer and parse it into the model for a location entry and put it in the database
     *
     * @return mixed
     */
    public function parseLocationTable($oldOffer) {
        $geolocationJson = json_decode($oldOffer->geolocation_v2);

        foreach($geolocationJson as $location) {
            $model = new Offer_Location();
            $model->address = $location->correctedAddress;
            $model->notes = $location->body;
            $model->name = $location->correctedAddress;
            $model->lat = $location->point->lat;
            $model->lng = $location->point->lng;
            $model->offer_id = $oldOffer->coupon_id;
            $model->countrywide = false;
            $model->save();
        }

    }

    /**
     * Take the information in the old offer model and parse it into new categories if the categories don't exist yet.
     * Returns the id of the first found category or first made category to be used in the new offer model
     *
     * @return integer
     */
    public function parseOfferCategoryTable($oldOffer) {
        $tagJson = json_decode($oldOffer->offer_tags);
        $firstCatId = null;

        foreach($tagJson as $tag) {
            try {
                $testCategory = Category_Translation::where('name', $tag->name)->where('slug', $tag->slug)->firstOrFail();

                $firstCatId = $testCategory->category_id;
            } catch (ModelNotFoundException $e) {
                $catModel = new Category();
                $catTranslationModel = new Category_Translation();

                $catModel->active = true;
                $catModel->save();

                $catTranslationModel->category_id = $catModel->id;
                $catTranslationModel->lang = 'en';
                $catTranslationModel->name = $tag->name;
                $catTranslationModel->slug = $tag->slug;
                $catTranslationModel->save();

                if ($firstCatId == null) {
                    $firstCatId = $catModel->id;
                }
            }
        }

        return $firstCatId;
    }

    /**
     * Take the information in the old offer model and parse it into a new translation, and save that model to the database
     *
     * @return mixed
     */
    public function parseOfferTranslationTable($oldOffer) {
        $model = new Offer_Translation();

        $model->content = html_entity_decode($oldOffer->terms);
        $model->location_notes = $oldOffer->null;
        $model->lang = 'en';
        $model->name = html_entity_decode($oldOffer->coupon_title);
        $model->slug = $oldOffer->coupon_slug;
        $model->brief = html_entity_decode($oldOffer->offer_highlight);
        $model->summary = html_entity_decode($oldOffer->coupon_offer);
        $this->info($oldOffer->id);
        $model->offer_id = $oldOffer->coupon_id;

        $model->save();
    }

    /**
     * Take the information in the old offer model and parse it into a new offer model, and save that model to the database
     *
     * @return mixed
     */
    public function parseOfferTable($oldOffer) {
        $model = new Offer();

        // Parse the id, dates, and merchant info from old offer
        $model->id = $oldOffer->coupon_id;
        $model->updated_at = $oldOffer->post_modified;
        $this->info($oldOffer->company_name);
        $this->info(html_entity_decode($oldOffer->company_name, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        $model->merchant_name = html_entity_decode($oldOffer->company_name, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $model->merchant_email = $oldOffer->company_email;
        if ($oldOffer->merchant_phone_prefix != '' && $oldOffer->merchant_phone_prefix != null) {
            $model->merchant_phone_prefix = $oldOffer->merchant_phone_prefix;
        }
        if ($oldOffer->merchant_phone_number != '' && $oldOffer->merchant_phone_number != null) {
            $model->merchant_phone_number = $oldOffer->merchant_phone_number;
        }
        $model->merchant_website = $oldOffer->company_website;

        // Get the merchant logo from old offer and convert it to id from new media table
        if ($oldOffer->company_logo != '' && $oldOffer->company_logo != null) {
            $companyLogoPath = explode('http://most.dev/wp-content/', $oldOffer->company_logo);
            $companyLogoPath = $companyLogoPath[1];
            try {
                $testModel = Media::where('file_path', $companyLogoPath)->firstOrFail();
                $model->merchant_logo = $testModel->id;
            } catch (ModelNotFoundException $e) {
                $this->error('company logo not found in media table...');
                $model->merchant_logo = null;
            }
        } else {
            $this->error("wtf happened? $oldOffer->company_logo");
        }



        // Get the valid start and end dates from old offer
        if ($oldOffer->valid_start == '') {
            $model->start_date = null;
        } else {
            $model->start_date = $oldOffer->valid_start;
        }
        if ($oldOffer->valid_end == '') {
            $model->end_date = null;
        } else {
            $model->end_date = $oldOffer->valid_end;
        }

        // Get the price range from old offer
        if ($oldOffer->price_range == '') {
            $model->price_range = null;
        } else {
            $model->price_range = (int)$oldOffer->price_range;
        }

        // Get small_business bool and cuisine types from old offer
        $model->small_business = $oldOffer->small_business;
        $model->cuisine_type = $oldOffer->cuisine_type;

        // Determine if the offer is still valid by checking the valid dates and comparing them with todays date
        $today = (int)date('Ymd');
        if ($oldOffer->date_options == 'List Start and End Dates') {
            if ($oldOffer->valid_start <= $today && $today <= $oldOffer->valid_end) {
                $model->active = true;
            } else {
                $model->active = false;
            }
        } else if ($oldOffer->date_options == 'List End Date') {
            if ($today <= $oldOffer->valid_end) {
                $model->active = true;
            } else {
                $model->active = false;
            }
        } else if ($oldOffer->date_options == 'Ongoing') {
            $model->active = true;
        } else {
            $model->active = false;
        }

        // Set the terms accepted to true since they're already in the old database
        $model->terms_accepted = true;

        // Parse and save the categories from the old offer into the new database and set the first category id to the offer category id
        $model->category_id = $this->parseOfferCategoryTable($oldOffer);

        $model->status = 'publish';

        $model->created_at = $oldOffer->new_offer_created_at;
        $model->published_at = $oldOffer->new_offer_published_at;
        $model->created_by = $oldOffer->new_offer_created_by;
        $model->updated_by = $oldOffer->new_offer_updated_by;

        // Parse the featured image and coupon images fields into the media table
        $this->parseOfferMediaTable($oldOffer);

        $model->save();
    }

    public function parseOfferMediaTable($oldOffer) {
        // get the featured image url and connect that to the media table and off_media table
        if ($oldOffer->featured_image != '' && $oldOffer->featured_image != null) {
            $featuredImageUrl = $oldOffer->featured_image;
            $featuredImagePath = explode('http://most.dev/wp-content/', $featuredImageUrl);
            $featuredImagePath = $featuredImagePath[1];
            try {
                $testModel = Media::where('file_path', $featuredImagePath)->firstOrFail();
                $offerMediaModel = new Offer_Media();
                $offerMediaModel->offer_id = $oldOffer->coupon_id;
                $offerMediaModel->media_id = $testModel->id;
                $offerMediaModel->save();
            } catch (ModelNotFoundException $e) {
                $this->error('no featured Image I guess');
            }
        }

        // get the coupon images and connect those to the media table and offer_media table
        if ($oldOffer->coupon_images != '' && $oldOffer->coupon_images != null) {
            $coupon_images = json_decode($oldOffer->coupon_images);
            foreach($coupon_images as $imageUrl) {
                $imageUrl = explode('http://most.dev/wp-content/', $imageUrl);
                $imageUrl = $imageUrl[1];
                try {
                    $testModel = Media::where('file_path', $imageUrl)->firstOrFail();
                    $offerMediaModel = new Offer_Media();
                    $offerMediaModel->offer_id = $oldOffer->coupon_id;
                    $offerMediaModel->media_id = $testModel->id;
                    $offerMediaModel->save();
                } catch (ModelNotFoundException $e) {
                    $this->error('no coupon image I guess');
                }
            }

        }
    }

    public function parseOfferMarkTable($oldOffer) {
        if ($oldOffer->marks != '' && $oldOffer->marks != null) {
            $marks = json_decode($oldOffer->marks);
            foreach ($marks as $mark) {
                try {
                    $testModel = Mark::where('id', $mark->id)->firstOrFail();

                    $offer_mark_entry = new Offer_Mark();
                    $offer_mark_entry->offer_id = $oldOffer->coupon_id;
                    $offer_mark_entry->mark_id = $testModel->id;
                    $offer_mark_entry->save();
                } catch (ModelNotFoundException $e) {
                    $mark_entry = new Mark();
                    $mark_translation_entry = new Mark_Translation();
                    $offer_mark_entry = new Offer_Mark();

                    $mark_entry->id = $mark->id;
                    $mark_entry->name = $mark->name;
                    $mark_entry->active = true;
                    if ($mark->acceptance_mark_image != '' && $mark->acceptance_mark_image != null) {
                        $imageUrl = $mark->acceptance_mark_image;
                        $imageUrl = explode('http://most.dev/wp-content/', $imageUrl);
                        $imageUrl = $imageUrl[1];
                        try {
                            $testModel = Media::where('file_path', $imageUrl)->firstOrFail();
                            $mark_entry->media_id = $testModel->id;
                        } catch (ModelNotFoundException $e) {
                            $this->error('no mark image I guess');
                        }
                    }
                    $mark_entry->save();

                    $mark_translation_entry->mark_id = $mark_entry->id;
                    $mark_translation_entry->lang = 'en';
                    $mark_translation_entry->summary = html_entity_decode($mark->terms);
                    $mark_translation_entry->slug = 'en-' . $mark->name;
                    $mark_translation_entry->save();

                    $offer_mark_entry->offer_id = $oldOffer->coupon_id;
                    $offer_mark_entry->mark_id = $mark_entry->id;
                    $offer_mark_entry->save();
                }
            }
        }
    }

    public function parseUserTable() {
        $today = new DateTime('NOW');
        $today = $today->format('Y-m-d H:i:s');

        $mostUsers = Most_User::all();

        $bar = $this->output->createProgressBar(count($mostUsers));
        foreach($mostUsers as $mostUser) {
            $modelUser = new User();
            $modelUser->name = $mostUser->user_login;
            $modelUser->username = $mostUser->user_login;
            $modelUser->first_name = $mostUser->user_login;
            $modelUser->last_name = '';
            $modelUser->last_login_date = null;
            $modelUser->email = $mostUser->user_email;
            $modelUser->password = null;
            $modelUser->is_sys_admin = false;
            $modelUser->is_active = true;
            $modelUser->phone = '';
            $modelUser->security_question = '';
            $modelUser->security_answer = '';
            $modelUser->confirm_code = 'y';
            $modelUser->default_app_id = null;
            $modelUser->oauth_provider = null;
            $modelUser->created_date = $mostUser->user_registered;
            $modelUser->last_modified_date = $today;
            $modelUser->created_by_id = 0;
            $modelUser->last_modified_by_id = 0;
            $modelUser->save();

            $userToRole = new user_to_app_to_role();
            $userToRole->user_id = $modelUser->id;
            $userToRole->app_id = 6;
            $userToRole->role_id = 7;
            $userToRole->save();


            $bar->advance();
        }
        $bar->finish();
    }

    public function parseManualEntries() {

    }

    /**
     * Take the old offer, and parse all of its information into their own models to be saved into the new database
     *
     * @return mixed
     */
    public function parseOldOffer($oldOffer) {
        $this->parseOfferTable($oldOffer);
        $this->parseOfferTranslationTable($oldOffer);
        $this->parseOfferCollectionTable($oldOffer);
        $this->parseLocationTable($oldOffer);
        $this->parseOfferMarkTable($oldOffer);
    }

    /**
     * Take the old offer json and create an entry in the database for it.
     *
     * @return mixed
     */
    public function storeOldOffer($oldOffer) {
        $model = new OldOffer();
        $model->coupon_id = $oldOffer->coupon_id;
        $model->coupon_title = $oldOffer->coupon_title;
        $model->coupon_slug = $oldOffer->coupon_slug;
        $model->post_modified = $oldOffer->post_modified;
        $model->offer_highlight = $oldOffer->offer_settings->offer_highlight;
        $model->coupon_offer = $oldOffer->offer_settings->coupon_offer;
        $model->company_name = $oldOffer->offer_settings->company_name;
        $model->date_options = $oldOffer->offer_settings->dates->date_options;
        $model->valid_start = $oldOffer->offer_settings->dates->valid_start;
        $model->valid_end = $oldOffer->offer_settings->dates->valid_end;
        $model->company_logo = $oldOffer->offer_settings->company_logo;
        $model->terms = $oldOffer->offer_settings->terms;
        $model->locations = json_encode($oldOffer->offer_settings->locations);
        $model->company_email = $oldOffer->offer_settings->company_email;
        try {
            $model->merchant_phone_prefix = $oldOffer->offer_settings->merchant_phone_prefix;
        } catch (ErrorException $e) {}
        try {
            $model->merchant_phone_number = $oldOffer->offer_settings->merchant_phone_number;
        } catch (ErrorException $e) {
            $model->merchant_phone_number = $oldOffer->offer_settings->company_phone_number;
        }
        if (is_array($oldOffer->geo_location) || is_object($oldOffer->geo_location)) {
            $model->address = $oldOffer->geo_location->address;
            $model->lat = $oldOffer->geo_location->lat;
            $model->lng = $oldOffer->geo_location->lng;
        } else {
            $model->address = "";
            $model->lat = 0.0;
            $model->lng = 0.0;
        }
        $model->geolocation_v2 = json_encode($oldOffer->geolocation_v2);
        try {
            $model->price_range = $oldOffer->price_range;
        } catch (\ErrorException $e) {
            $model->price_range = null;
        }
        try {
            $model->small_business = $oldOffer->small_business;
        } catch (\ErrorException $e) {
            $model->small_business = null;
        }
        try {
            $model->cuisine_type = $oldOffer->cuisine_type;
        } catch (\ErrorException $e) {
            $model->cuisine_type = null;
        }
        $model->offer_tags = json_encode($oldOffer->offer_tags);
        $model->offer_destination = json_encode($oldOffer->offer_destination);
        $model->featured_image = $oldOffer->offer_settings->featured_image;
        $model->coupon_images = json_encode($oldOffer->offer_settings->coupon_images);
        try {
            $model->marks = json_encode($oldOffer->offer_settings->marks);
        } catch (ErrorException $e) {
            $this->error('no mark');
        }

        $model->new_offer_created_at = $oldOffer->new_offer_created_at;
        $model->new_offer_published_at = $oldOffer->new_offer_published_at;
        $model->new_offer_created_by = $oldOffer->new_offer_created_by;
        $model->new_offer_updated_by = $oldOffer->new_offer_updated_by;

        $this->parseOldOffer($model);

        $model->save();
    }
}
