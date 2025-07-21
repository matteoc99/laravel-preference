<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Support\Facades\Event;
use Matteoc99\LaravelPreference\Events\AllPreferencesDeletedEvent;
use Matteoc99\LaravelPreference\Events\PreferenceDeletedEvent;
use Matteoc99\LaravelPreference\Events\PreferenceUpdatedEvent;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;

class PreferenceEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test preference
        PreferenceBuilder::init(General::LANGUAGE)
            ->withDefaultValue('en')
            ->create();
    }

    protected function tearDown(): void
    {
        // Clean up the test preference
        PreferenceBuilder::delete(General::LANGUAGE);

        parent::tearDown();
    }

    /** @test */
    public function it_dispatches_update_event_when_preference_is_set()
    {
        // Fake the event dispatcher
        Event::fake([PreferenceUpdatedEvent::class]);

        // Set a preference
        $this->testUser->setPreference(General::LANGUAGE, 'de');

        // Assert that the event was dispatched
        Event::assertDispatched(PreferenceUpdatedEvent::class, function ($event) {
            return $event->model->id === $this->testUser->id
                && $event->preferenceGroup === General::LANGUAGE
                && $event->oldValue === 'en'
                && $event->newValue === 'de';
        });
    }

    /** @test */
    public function it_dispatches_delete_event_when_preference_is_removed()
    {
        // Set a preference first
        $this->testUser->setPreference(General::LANGUAGE, 'it');

        // Fake the event dispatcher
        Event::fake([PreferenceDeletedEvent::class]);

        // Remove the preference
        $this->testUser->removePreference(General::LANGUAGE);

        // Assert that the event was dispatched
        Event::assertDispatched(PreferenceDeletedEvent::class, function ($event) {
            return $event->model->id === $this->testUser->id
                && $event->preferenceGroup === General::LANGUAGE
                && $event->oldValue === 'it';
        });
    }

    /** @test */
    public function it_dispatches_all_deleted_event_when_all_preferences_are_removed()
    {
        // Set some preferences first
        $this->testUser->setPreference(General::LANGUAGE, 'de');

        // Create another test preference
        PreferenceBuilder::init(General::THEME)
            ->withDefaultValue('light')
            ->create();
        $this->testUser->setPreference(General::THEME, 'dark');

        // Fake the event dispatcher
        Event::fake([AllPreferencesDeletedEvent::class]);

        // Remove all preferences
        $this->testUser->removeAllPreferences();

        // Assert that the event was dispatched
        Event::assertDispatched(AllPreferencesDeletedEvent::class, function ($event) {
            return $event->model->id === $this->testUser->id;
        });

        // Clean up the additional test preference
        PreferenceBuilder::delete(General::THEME);
    }
}
