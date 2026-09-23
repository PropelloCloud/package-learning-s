<?php

use Illuminate\Database\Eloquent\Model;
use Propello\PackageLearningS\ChangeDetector;

function modelWithChanges(array $changes, array $original): Model
{
    $model = Mockery::mock(Model::class);
    $model->allows('getChanges')->andReturn($changes);
    $model->allows('getOriginal')->andReturn($original);
    return $model;
}

// getUpdatedValues — false positives
it('treats 0 and false as equal', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['active' => false], ['active' => 0])
    );
    expect($new)->toBeEmpty();
    expect($old)->toBeEmpty();
});

it('treats 1 and true as equal', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['active' => true], ['active' => 1])
    );
    expect($new)->toBeEmpty()
        ->and($old)->toBeEmpty();
});

it('treats 0.00 and 0 as equal', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['amount' => 0.00], ['amount' => 0])
    );
    expect($new)->toBeEmpty()
        ->and($old)->toBeEmpty();
});

it('treats null and null as equal', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['name' => null], ['name' => null])
    );
    expect($new)->toBeEmpty()
        ->and($old)->toBeEmpty();
});

// getUpdatedValues — real changes
it('detects a real string change', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['name' => 'bar'], ['name' => 'foo'])
    );
    expect($new)->toBe(['name' => 'bar'])
        ->and($old)->toBe(['name' => 'foo']);
});

it('treats null and empty string as different', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['name' => ''], ['name' => null])
    );
    expect($new)->toBe(['name' => '']);
});

it('treats null and 0 as different', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['amount' => 0], ['amount' => null])
    );
    expect($new)->toBe(['amount' => 0]);
});

it('treats numeric string and integer as equal', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['count' => 1], ['count' => '1'])
    );
    expect($new)->toBeEmpty()
        ->and($old)->toBeEmpty();
});

it('excludes updated_at from getUpdatedValues', function () {
    $detector = new ChangeDetector();
    [$old, $new] = $detector->getUpdatedValues(
        modelWithChanges(['updated_at' => '2026-01-02'], ['updated_at' => '2026-01-01'])
    );
    expect($new)->toBeEmpty()
        ->and($old)->toBeEmpty();
});

// filterAttributes
it('excludes updated_at from attributes', function () {
    $detector = new ChangeDetector();
    $result = $detector->filterAttributes(['name' => 'foo', 'updated_at' => '2026-01-01']);
    expect($result)->toBe(['name' => 'foo']);
});

it('keeps all other attributes', function () {
    $detector = new ChangeDetector();
    $result = $detector->filterAttributes(['name' => 'foo', 'active' => true]);
    expect($result)->toBe(['name' => 'foo', 'active' => true]);
});
