import { mount } from 'svelte';
import './fixture.css';
import Fixture from './Fixture.svelte';

mount(Fixture, { target: document.getElementById('app')! });
