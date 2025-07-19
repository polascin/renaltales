<?php echo $this->include('header'); ?>

<div class="hero-section">
    <h1>{{ welcome_title }}</h1>
    <p>{{ welcome_message }}</p>
    <a href="/get-started" >{{ get_started_text }}</a>
</div>

<div class="features">
    <div class="feature">
        <h3>{{ feature1_title }}</h3>
        <p>{{ feature1_description }}</p>
    </div>
    <div class="feature">
        <h3>{{ feature2_title }}</h3>
        <p>{{ feature2_description }}</p>
    </div>
    <div class="feature">
        <h3>{{ feature3_title }}</h3>
        <p>{{ feature3_description }}</p>
    </div>
</div>

<?php echo $this->include('footer'); ?>

