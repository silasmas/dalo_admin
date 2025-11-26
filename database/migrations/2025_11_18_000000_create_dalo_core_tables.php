<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------
        // appt_appointments
        // ------------------------------
        Schema::create('appt_appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('user_id'); // client
            $table->unsignedBigInteger('staff_user_id')->nullable(); // facultatif
            $table->string('provider_name', 191)->nullable(); // si pas de staff_user_id

            $table->dateTime('scheduled_at');
            $table->dateTime('end_at');
            $table->enum('app_status', ['pending','confirmed','canceled','done','no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->integer('canceled_by')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->string('titre', 200)->comment('Raison du rendez-vous');
            $table->string('description', 1000)->nullable()->comment('Details');

            $table->index('user_id', 'idx_appt_appts_user');
            $table->index('service_id', 'idx_appt_appts_service');
            $table->index('staff_user_id', 'idx_appt_appts_staff');
            $table->index('app_status', 'idx_appt_appts_state');
            $table->index('scheduled_at', 'idx_appt_appts_time');
        });

        // ------------------------------
        // appt_availabilities
        // ------------------------------
        Schema::create('appt_availabilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->tinyInteger('weekday')->comment('1=Lundi … 7=Dimanche');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration')->comment('Durée (minutes) d’un RDV type');
            $table->integer('capacity')->nullable()->comment('nb de RDV simultanés max sur ce créneau');
            $table->integer('service_id')->nullable();
            $table->integer('staff_user_id')->nullable();
        });

        // ------------------------------
        // don_donations
        // ------------------------------
        Schema::create('don_donations', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('status')->default(1)->comment('0=inactive,1=active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('donateur connecté');
            $table->unsignedInteger('subscription_id')->nullable()->comment('si ce don provient d une souscription');
            $table->unsignedBigInteger('donation_type_id');
            $table->decimal('amount', 16, 3);
            $table->string('currency', 4)->default('USD');
            $table->string('donor_name', 191)->nullable();
            $table->string('donor_email', 191)->nullable();
            $table->string('donor_phone', 50)->nullable();
            $table->enum('donation_status', ['pending','paid','failed','canceled','refunded'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->unsignedInteger('payment_id')->nullable()->comment('lien vers main_payments.id');
            $table->string('reference', 64)->nullable()->comment('DON-2025-00001 (unique)');
            $table->text('notes')->nullable();

            $table->unique('reference', 'uk_don_donations_ref');
            $table->index('user_id', 'idx_don_donations_user');
            $table->index('subscription_id', 'idx_don_donations_subscription');
            $table->index('donation_status', 'idx_don_donations_donation_status');
            $table->index('paid_at', 'idx_don_donations_paid');
            $table->index('donation_type_id', 'idx_don_donations_types');
            $table->index('payment_id', 'idx_don_donations_payment');
        });

        // ------------------------------
        // don_subscriptions
        // ------------------------------
        Schema::create('don_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('status')->default(1)->comment('0=inactive,1=active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('code', 32);
            $table->unsignedBigInteger('subscription_type_id');
            $table->unsignedBigInteger('donation_type_id');
            $table->decimal('amount', 16, 2);
            $table->string('currency', 4)->default('USD');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('state', ['active','paused','canceled','completed'])->default('active');
            $table->dateTime('next_due_at')->nullable();
            $table->dateTime('last_paid_at')->nullable();
            $table->enum('cycle', ['monthly','annualy','weekly'])->default('monthly')->comment('cycle de paiment');
            $table->tinyInteger('autopay')->default(0)->comment('1 si prélèvement auto');
            $table->text('notes')->nullable();

            $table->unique('code', 'uk_don_subscriptions_code');
            $table->index('user_id', 'idx_don_subscriptions_user');
            $table->index('state', 'idx_don_subscriptions_state');
            $table->index('next_due_at', 'idx_don_subscriptions_next_due');
            $table->index(['subscription_type_id','donation_type_id'], 'idx_don_subscriptions_types');
        });

        // ------------------------------
        // edm_videos
        // ------------------------------
        Schema::create('edm_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->string('author_name', 191)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->json('hashtags_json')->nullable();
            $table->string('url_video', 500);
            $table->string('cover_url', 500)->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->tinyInteger('featured')->default(0);
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('favorites_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->integer('shares_count')->default(0);

            $table->index('status', 'idx_edm_videos_status');
            $table->index(['author_user_id','author_name'], 'idx_edm_videos_author');
            $table->index('featured', 'idx_edm_videos_featured');
            $table->index('category_id', 'idx_edm_videos_category');
        });

        // ------------------------------
        // edm_video_comments
        // ------------------------------
        Schema::create('edm_video_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('content');

            $table->index('video_id', 'idx_edm_video_comments_video');
            $table->index('user_id', 'idx_edm_video_comments_user');
            $table->index('parent_id', 'idx_edm_video_comments_parent');
        });

        // ------------------------------
        // edm_video_favorites
        // ------------------------------
        Schema::create('edm_video_favorites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('user_id');

            $table->unique(['video_id','user_id'], 'uk_edm_video_likes');
            $table->index('user_id', 'idx_edm_video_likes_user');
        });

        // ------------------------------
        // edm_video_likes
        // ------------------------------
        Schema::create('edm_video_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('user_id');

            $table->unique(['video_id','user_id'], 'uk_edm_video_likes');
            $table->index('user_id', 'idx_edm_video_likes_user');
        });

        // ------------------------------
        // gal_galleries
        // ------------------------------
        Schema::create('gal_galleries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug', 191)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->enum('visibility', ['public','private'])->default('public');
            $table->unsignedBigInteger('images_count')->default(0);

            $table->unique('slug', 'uk_gal_galleries_slug');
            $table->index('status', 'idx_gal_galleries_status');
            $table->index('parent_id', 'idx_gal_galleries_parent');
            $table->index('visibility', 'idx_gal_galleries_visibility');
        });

        // ------------------------------
        // gal_images
        // ------------------------------
        Schema::create('gal_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('gallery_id');
            $table->string('title', 255)->nullable();
            $table->text('caption')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('file_url', 1000);
            $table->unsignedBigInteger('bytes')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->dateTime('taken_at')->nullable();

            $table->index('gallery_id', 'idx_gal_images_gallery');
            $table->index('status', 'idx_gal_images_status');
            $table->index('gallery_id', 'idx_gal_images_sort');
        });

        // ------------------------------
        // main_categories
        // ------------------------------
        Schema::create('main_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('type', 64);
            $table->string('cat_key', 128);
            $table->string('cat_name', 191);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->json('metadata')->nullable();

            $table->unique(['type','cat_key'], 'uk_main_categories');
            $table->unique(['type','cat_key'], 'uk_main_categories_type_key');
            $table->index('type', 'idx_main_categories_type');
            $table->index('parent_id', 'idx_main_categories_parent');
        });

        // ------------------------------
        // main_payments
        // ------------------------------
        Schema::create('main_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->tinyInteger('payment_status')->comment('1 = en cours, 2 = approuvé, 3 = rejeté');
            $table->decimal('amount', 16, 3);
            $table->decimal('total_amount', 16, 3)->default(0)->comment('Montant total facturé');
            $table->string('currency', 4);
            $table->unsignedInteger('order_id')->nullable();
            $table->unsignedInteger('donation_id')->nullable();
            $table->enum('method', ['mobile_money','card']);
            $table->string('channel', 50)->nullable();
            $table->string('telephone', 50);
            $table->string('ip_address', 100)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('provider_reference', 1000)->nullable();
            $table->string('order_number', 1000)->nullable();
            $table->string('gateway', 100)->nullable();

            $table->index('payment_status');
            $table->index('order_id');
            $table->index('donation_id', 'subscription_id');
            $table->index('created_by');
        });

        // ------------------------------
        // main_users
        // ------------------------------
        Schema::create('main_users', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->tinyInteger('status')->comment('1 = activated, 2 = pending, ...');
            $table->integer('created_by')->default(0);
            $table->integer('default_role')->comment('1=Admin, 2=>Pasteur, 5=>Utilisateur simple');
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('username', 100)->nullable();
            $table->string('phone', 20);
            $table->string('email', 250)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M','F'])->nullable();
            $table->string('password', 255)->nullable();
            $table->dateTime('last_activity')->nullable();
            $table->string('otp', 255)->nullable();
            $table->string('country', 5)->nullable();
            $table->string('city', 250)->nullable();
            $table->string('profile', 250)->nullable();
            $table->string('public_token', 1000)->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->string('fcm_token', 1000)->nullable();

            $table->unique('phone');
            $table->unique('username', 'user_name');
        });

        // ------------------------------
        // msg_messages
        // ------------------------------
        Schema::create('msg_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('channel', ['contact','support','feedback','prayer_request','other'])->default('contact');
            $table->unsignedBigInteger('from_id')->nullable();
            $table->string('from_name', 512)->nullable();
            $table->string('from_email', 512)->nullable();
            $table->string('from_phone', 50)->nullable();
            $table->string('subject', 255);
            $table->longText('body');
            $table->json('attachments_json')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->tinyInteger('priority')->nullable();

            $table->index('channel', 'idx_msg_messages_channel');
            $table->index('parent_id', 'idx_msg_messages_parent');
        });

        // ------------------------------
        // news_posts
        // ------------------------------
        Schema::create('news_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('status')->default(1)->comment('0=inactive, 1=active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('title', 255);
            $table->string('slug', 191)->nullable();
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->enum('body_format', ['markdown','html','text'])->default('text');
            $table->string('cover_url', 500)->nullable();
            $table->enum('news_status', ['draft','scheduled','published','archived'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->tinyInteger('featured')->default(0);
            $table->string('location_name', 191)->nullable();
            $table->string('location_addr', 255)->nullable();
            $table->string('external_url', 500)->nullable();
            $table->unsignedBigInteger('read_count')->default(0);

            $table->unique('slug', 'uk_news_posts_slug');
            $table->index('category_id', 'idx_news_posts_category');
            $table->index(['news_status','published_at'], 'idx_news_posts_state_pub');
            $table->index(['starts_at','ends_at'], 'idx_news_posts_dates');
            $table->index('featured', 'idx_news_posts_featured');
        });

        // ------------------------------
        // qdb_answers
        // ------------------------------
        Schema::create('qdb_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('question_id');
            $table->longText('answer');
            $table->enum('answer_status', ['published','hidden','draft'])->default('published');
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->string('author_name', 191)->nullable();
            $table->string('author_email', 191)->nullable();
            $table->tinyInteger('is_official')->default(0);
            $table->tinyInteger('is_accepted')->default(0);
            $table->integer('nb_likes');
            $table->json('versets_refs_json')->nullable();
            $table->json('sources_json')->nullable();

            $table->index('question_id', 'idx_qdb_answers_question');
            $table->index('author_user_id', 'idx_qdb_answers_author');
            $table->index('answer_status', 'idx_qdb_answers_response_status');
        });

        // ------------------------------
        // qdb_likes
        // ------------------------------
        Schema::create('qdb_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->integer('question_id')->nullable();
            $table->integer('answer_id')->nullable();
        });

        // ------------------------------
        // qdb_questions
        // ------------------------------
        Schema::create('qdb_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('title', 255);
            $table->string('slug', 191)->nullable();
            $table->longText('body');
            $table->enum('state', ['draft','published','hidden','archived'])->default('published');
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->string('author_name', 191)->nullable();
            $table->string('author_email', 191)->nullable();
            $table->string('author_phone', 191)->nullable();
            $table->unsignedBigInteger('answer_id')->nullable();
            $table->integer('sermon_id')->nullable();
            $table->integer('edm_id')->nullable();
            $table->integer('nb_likes');
            $table->string('categories_tags', 2000)->nullable();

            $table->unique('slug', 'uk_qdb_questions_slug');
            $table->index('state', 'idx_qdb_questions_state');
            $table->index('author_user_id', 'idx_qdb_questions_author');
            $table->index('created_at', 'idx_qdb_questions_created_at');
        });

        // ------------------------------
        // sermon_items
        // ------------------------------
        Schema::create('sermon_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->enum('type', ['predication','enseignement','emission']);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('preacher_name', 191)->nullable();
            $table->unsignedBigInteger('preacher_user_id')->nullable();
            $table->date('preached_on')->nullable();
            $table->tinyInteger('is_live')->default(0);
            $table->tinyInteger('featured')->default(0);
            $table->json('formats')->nullable();
            $table->tinyInteger('has_video')->default(0);
            $table->tinyInteger('has_audio')->default(0);
            $table->tinyInteger('has_text')->default(0);
            $table->string('url_video', 500)->nullable();
            $table->string('url_audio', 500)->nullable();
            $table->longText('content_text')->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);

            $table->index('type', 'idx_sermon_items_type');
            $table->index('preached_on', 'idx_sermon_items_preached_on');
            $table->index(['preacher_user_id','preacher_name'], 'idx_sermon_items_preacher');
            $table->index('featured', 'idx_sermon_items_featured');
            $table->index(['is_live','start_at'], 'idx_sermon_items_live');
            $table->index('has_video', 'idx_sermon_items_formats_video');
            $table->index('has_audio', 'idx_sermon_items_formats_audio');
            $table->index('has_text', 'idx_sermon_items_formats_text');
            $table->index('category_id', 'idx_sermon_items_category');
        });

        // ------------------------------
        // services
        // ------------------------------
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('name', 191);
            $table->string('slug', 191)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('magener_id')->default(0)->comment('User ID du pasteur / serviteur');

            $table->unique('slug', 'uk_appt_services_slug');
            $table->index('status', 'idx_appt_services_status');
        });

        // ------------------------------
        // shop_orders
        // ------------------------------
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('code', 32);
            $table->enum('state', ['pending','paid','failed','canceled','shipped','completed'])->default('pending');
            $table->char('currency', 3)->default('USD');
            $table->decimal('subtotal', 16, 2)->unsigned()->default(0);
            $table->decimal('discount', 16, 2)->unsigned()->default(0);
            $table->decimal('shipping', 16, 2)->unsigned()->default(0);
            $table->decimal('total', 16, 2)->unsigned()->default(0);
            $table->dateTime('paid_at')->nullable();
            $table->unsignedBigInteger('payment_id')->default(0);
            $table->string('shipping_name', 191)->nullable();
            $table->string('shipping_phone', 50)->nullable();
            $table->string('shipping_addr', 255)->nullable();
            $table->string('shipping_ref', 255)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_country', 100)->nullable();
            $table->text('shipping_notes')->nullable();

            $table->unique('code', 'uk_shop_orders_code');
            $table->index('user_id', 'idx_shop_orders_user');
            $table->index('state', 'idx_shop_orders_state');
            $table->index('paid_at', 'idx_shop_orders_paid');
        });

        // ------------------------------
        // shop_order_items
        // ------------------------------
        Schema::create('shop_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_title', 255);
            $table->string('sku', 64)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->tinyInteger('is_digital')->default(0);
            $table->string('download_url', 500)->nullable();
            $table->json('chosen_options')->nullable();

            $table->index('order_id', 'idx_shop_order_items_order');
        });

        // ------------------------------
        // shop_products
        // ------------------------------
        Schema::create('shop_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('sku', 64)->nullable();
            $table->string('slug', 191)->nullable();
            $table->string('title', 255);
            $table->longText('description')->nullable();
            $table->enum('product_type', ['book','accessories','clothes','other'])->default('other');
            $table->tinyInteger('is_digital')->default(0);
            $table->unsignedBigInteger('price')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->integer('stock_qty')->default(0);
            $table->json('attributes_json')->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->text('images')->nullable();
            $table->string('file_url', 500)->nullable();

            $table->unique('sku', 'uk_shop_products_sku');
            $table->unique('slug', 'uk_shop_products_slug');
            $table->index('status', 'idx_shop_products_status');
            $table->index('category_id', 'idx_shop_products_category');
            $table->index('product_type', 'idx_shop_products_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_orders');
        Schema::dropIfExists('services');
        Schema::dropIfExists('sermon_items');
        Schema::dropIfExists('qdb_questions');
        Schema::dropIfExists('qdb_likes');
        Schema::dropIfExists('qdb_answers');
        Schema::dropIfExists('news_posts');
        Schema::dropIfExists('msg_messages');
        Schema::dropIfExists('main_users');
        Schema::dropIfExists('main_payments');
        Schema::dropIfExists('main_categories');
        Schema::dropIfExists('gal_images');
        Schema::dropIfExists('gal_galleries');
        Schema::dropIfExists('edm_video_likes');
        Schema::dropIfExists('edm_video_favorites');
        Schema::dropIfExists('edm_video_comments');
        Schema::dropIfExists('edm_videos');
        Schema::dropIfExists('don_subscriptions');
        Schema::dropIfExists('don_donations');
        Schema::dropIfExists('appt_availabilities');
        Schema::dropIfExists('appt_appointments');
    }
};
