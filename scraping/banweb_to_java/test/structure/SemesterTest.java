package structure;

import org.junit.Assert;
import org.junit.Test;

import main.java.structure.Semester;

public class SemesterTest {
	
	@Test
	public void getSchoolYear_success()
	{
		Assert.assertEquals(new Integer(2016), (new Semester("201610")).getSchoolYear());
		Assert.assertEquals(new Integer(2016), (new Semester("201620")).getSchoolYear());
		Assert.assertEquals(new Integer(2016), (new Semester("201630")).getSchoolYear());
	}
	
	@Test
	public void getCalendarYear_success()
	{
		Assert.assertEquals(new Integer(2015), (new Semester("201610")).getCalendarYear());
		Assert.assertEquals(new Integer(2015), (new Semester("201620")).getCalendarYear());
		Assert.assertEquals(new Integer(2016), (new Semester("201630")).getCalendarYear());
	}
	
	@Test
	public void getSemesterIndex_success()
	{
		Assert.assertEquals(new Integer(10), (new Semester("201610")).getSemesterIndex());
		Assert.assertEquals(new Integer(20), (new Semester("201620")).getSemesterIndex());
		Assert.assertEquals(new Integer(30), (new Semester("201630")).getSemesterIndex());
	}
}
